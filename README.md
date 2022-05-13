# Mailgun Mautic Plugin

### Requirements

- Mautic v2.15 or higher;
- PHP v7.0 or higher.

### Installation

```sh
$ cd plugins

$ git clone https://github.com/nextgi/MauticMailgunBundle.git
```

Clean the cache by rolling or following command in the root paste do seu Mautic:

```sh
$ php app/console cache:clear && chmod -R g+rw * && php app/console mautic:assets:generate && php app/console mautic:plugins:reload
```

Access the plugins page hair painel do Mautic and click on the **Install/Update plugins** button.

## How to use

- Select the 'Mailgun' email sending service in Settings > Email Settings.
- Enter your user name and see what you can find in your Mailgun account
    + postmaster@mg.yourmailgundomain.com
- Save / Apply

### Callback Webhook

Add to URL to follow for all non-Mailgun events to follow:
- URL: `https://yourmautic.com/mailer/mailgun/callback`.
- Events:
    + complained
    + permanent_fail
    + temporary_fail
    + unsubscribed

## Credits
This was inspired by https://github.com/moskoweb/AMMailgunBundle
