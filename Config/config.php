<?php
return [
    'services'   => [
        'other' => [
            'mautic.transport.mailgun' => [
                'class'        => 'MauticPlugin\MauticMailgunBundle\Swiftmailer\Transport\MailgunTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'mautic.email.model.transport_callback',
                    '%mautic.mailer_mailgun_sandbox%',
                    '%mautic.mailer_mailgun_sandbox_default_mail%',
                ],
                'methodCalls'  => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
        ],
    ],
    'parameters' => [
        'mailer_mailgun_sandbox'              => false,
        'mailer_mailgun_sandbox_default_mail' => null,
    ],
];
