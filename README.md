Dropbox API handler for Nette Framework.
========================================

Needs to be said this component is based on a great API which can be found here: https://github.com/jimdoescode/CodeIgniter-Dropbox-API-Library

Implementation
--------------

Register in bootstrap.php:

    Echo511\Dropbox\CompilerExtension::register($configurator);

Configure config.neon:

    common:
            dropbox:
                    # Identity
                    key: ''
                    secret: ''

                    # OAuthStorage
                    oauthStorage: @dropbox.singleUser

                    # Sandbox for app folder access
                    # Dropbox for full access
                    defaultRoot: 'sandbox' | 'dropbox'

                    # Use Nette's panel for dumping responses
                    panel: true


Usage
-----

Dropbox provides lifetime access token for your app once user allows it. Therefore is useful to store this token (actually there are two: token, token_secret) for further use.

Storage based on session for single user is already included. In presenter the actuall call for this storage would look like:

    $rooftop = $this->context->dropbox->rooftop->getOAuthStorage() // Get storage
                                               ->authorize(); // This method calls $rooftop->setOAuthAccess();

Then you can call these:

    // Get account information
    $rooftop->call('account');

    // Server/Client
    $rooftop->call('add', $dropboxRelativePath, $localAbsolutePath, $options);
    $rooftop->call('get', $localAbsolutePath, $dropboxRelativePath);
    $rooftop->call('thumbnails', $localAbsolutePath, $dropboxRelativePath, $options);

    // Searching & Metadata
    $rooftop->call('search', $dropboxRelativePath, $query, $options);
    $rooftop->call('metadata', $dropboxRelativePath, $options);
    $rooftop->call('revisions', $dropboxRelativePath, $options);

    // Server side operations
    $rooftop->call('create_folder', $dropboxRelativePath);
    $rooftop->call('move', $dropboxFrom, $dropboxTo);
    $rooftop->call('copy', $dropboxFrom, $dropboxTo);
    $rooftop->call('delete', $dropboxRelativePath);
    $rooftop->call('restore', $dropboxRelativePath, $rev);

    // Public links
    $rooftop->call('shares', $dropboxRelativePath);
    $rooftop->call('media', $dropboxRelativePath);

    // Synchronise
    $rooftop->call('delta', $cursor);
    $rooftop->call('synchronise', $localAbsolutePath);


More info
---------
https://www.dropbox.com/developers/reference/api