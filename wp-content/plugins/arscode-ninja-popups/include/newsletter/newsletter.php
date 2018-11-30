<?php

/**
 * Class RelioNewsletterApi
 */
class RelioNewsletterApi {
    /**
     * @param $email
     * @param $firstname
     * @param $lastname
     * @param $lists
     * @param $additional
     * @param null $status
     * @return array|bool
     */
    public function subscribe($email, $firstname, $lastname, $lists, $additional, $status = null)
    {
        $newsletter = Newsletter::instance();

        $newsletterSubscription = NewsletterSubscription::instance();

        // Messages
        $options = get_option('newsletter', array());

        // Form field configuration
        $options_profile = get_option('newsletter_profile', array());

        $opt_in = (int) $newsletterSubscription->options['noconfirmation']; // 0 - double, 1 - single

        $email = $newsletter->normalize_email($email);

        // Shound never reach this point without a valid email address
        if ($email == null) {
            die('Wrong email');
        }

        $user = $newsletter->get_user($email);

        if ($user != null) {
            $newsletterSubscription->logger->info('Subscription of an address with status ' . $user->status);

            // Bounced
            if ($user->status == 'B') {
                // Non persistent status to decide which message to show (error)
                $user->status = 'E';
                return $user;
            }

            // If asked to put in confirmed status, do not check further
            if ($status != 'C' && $opt_in == 0) {

                // Already confirmed
                //if ($opt_in == 0 && $user->status == 'C') {
                if ($user->status == 'C') {

                    set_transient($user->id . '-' . $user->token, $_REQUEST, 3600 * 48);

                    // A second subscription always require confirmation otherwise anywan can change other users' data
                    $user->status = 'S';

                    $prefix = 'confirmation_';

                    if (empty($options[$prefix . 'disabled'])) {
                        $message = $options[$prefix . 'message'];

                        // TODO: This is always empty!
                        //$message_text = $options[$prefix . 'message_text'];
                        $subject = $options[$prefix . 'subject'];

                        $newsletterSubscription->mail($user->email, $newsletter->replace($subject, $user), $newsletter->replace($message, $user));
                    }

                    return $user;
                }
            }
        }

        if ($user != null) {
            $newsletterSubscription->logger->info("Email address subscribed but not confirmed");
            $user = array('id' => $user->id);
        } else {
            $newsletterSubscription->logger->info("New email address");
            $user = array('email' => $email);
        }

        $user['name'] = $newsletterSubscription->normalize_name($firstname);
        $user['surname'] = $newsletterSubscription->normalize_name($lastname);
        $user['token'] = $newsletter->get_token();
        $user['ip'] = $_SERVER['REMOTE_ADDR'];
        foreach ($lists as $k => $v) {
            $user['list_'.trim($v)] = 1;
        }
        if (count($additional) > 0) {
            foreach ($additional as $k => $v) {
                $user[$k] = $v;
            }
        }
        if ($status != null) {
            $user['status'] = $status;
        } else {
            $user['status'] = $opt_in == 1 ? 'C' : 'S';
        }

        $user = apply_filters('newsletter_user_subscribe', $user);
        // TODO: should be removed!!!
        if (defined('NEWSLETTER_FEED_VERSION')) {
            $options_feed = get_option('newsletter_feed', array());
            if ($options_feed['add_new'] == 1) {
                $user['feed'] = 1;
            }
        }

        $user = $newsletter->save_user($user);

        // Notification to admin (only for new confirmed subscriptions)
        if ($user->status == 'C') {
            do_action('newsletter_user_confirmed', $user);
            $newsletterSubscription->notify_admin($user, 'Newsletter subscription');
            setcookie('newsletter', $user->id . '-' . $user->token, time() + 60 * 60 * 24 * 365, '/');
        }

        $prefix = ($user->status == 'C') ? 'confirmed_' : 'confirmation_';

        if (empty($options[$prefix . 'disabled'])) {
            $message = $options[$prefix . 'message'];

            // TODO: This is always empty!
            //$message_text = $options[$prefix . 'message_text'];
            $subject = $options[$prefix . 'subject'];

            $newsletterSubscription->mail($user->email, $newsletter->replace($subject, $user), $newsletter->replace($message, $user));
        }

        return $user;
    }
}