# Typo3 TER Github Webhook

This is a simple GitHub Webhook Listener that automatically uploads a TYPO3 extension to the TER.  
The extension is only uploaded to the TER if a tag has been associated with the push.

[![Build Status](https://travis-ci.org/Sethorax/typo3-ter-webhook.svg?branch=master)](https://travis-ci.org/Sethorax/typo3-ter-webhook)
[![StyleCI](https://styleci.io/repos/91013782/shield?branch=master)](https://styleci.io/repos/91013782)

## Installation

To get started, simply run the following command:

> `composer create-project sethorax/typo3-ter-webhook`

Or you can clone this repo and run `composer install` manually.

Once the project is created you need to create a **config.yml** file in the project root to configure the project.

The file should look something like this:

```YAML
authorization:
    github:
        secret: GitHubWebhookSecret
    typo3:
        username: Typo3OrgUser
        password: MySuperSecretPassword

notification:
    slack:
        webhook-url: https://hooks.slack.com/services/XXX/XXX/XXX
```

The example above should be pretty self explanatory.  
Just specify your GitHub Webhook Secret, your typo3.org username and password.  
You can also specify a slack webhook url if you want to receive notifications on slack.

Once that is done you need to configure the Webhook for your GitHub repository.  
To do that simply go to the settings of the repository and navigate to **Webhooks**. Add a new Webhook there and paste the URL of your server into the Payload URL field. Provide the secret key that you entered in config.yml. The rest can stay as is it is.  
Click on *Add webhook* and you are done.

Now everytime you add a new tag to your repository the webhook will clone the repo, zip it and upload it to the TER. Don't forget to update the version number in `ext_emconf.php` otherwise the extension will not be uploaded!