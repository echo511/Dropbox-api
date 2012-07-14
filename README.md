Dropbox API handler for Nette Framework.
========================================

Needs to be said this component is based on great API which can be found here: https://github.com/jimdoescode/CodeIgniter-Dropbox-API-Library

Configure config.neon:
----------------------

        common:
                dropbox:
                        # Identity
                        key: 'kw0bhw9d843pkrb'
                        secret: '5dwrjczawd5nh5i'

                        # Sandbox for app folder access
                        # Dropbox for full access
                        defaultRoot: 'sandbox' | 'dropbox'

                        # Use Nette's panel for dumping responses
                        panel: true


Usage
-----

You do not need to worry about login etc. The handler will automatically attempt to login user by using redirects. Therefore is not recommend to use call function when processing forms etc. because after redirect your $_POST data are lost.

Best approach is to call in Presenter's startup() call $this->dropbox->getApi();. This will not send any requests to Dropbox server unless user is not authenticated.


Possible calls:
---------------

        // Get account information
        $this->dropbox->call('account');

        // Server/Client
        $this->dropbox->call('add', $dropboxRelativePath, $localAbsolutePath, $options);
        $this->dropbox->call('get', $localAbsolutePath, $dropboxRelativePath);
        $this->dropbox->call('thumbnails', $localAbsolutePath, $dropboxRelativePath, $options);

        // Searching & Metadata
        $this->dropbox->call('search', $dropboxRelativePath, $query, $options);
        $this->dropbox->call('metadata', $dropboxRelativePath, $options);
        $this->dropbox->call('revisions', $dropboxRelativePath, $options);

        // Server side operations
        $this->dropbox->call('create_folder', $dropboxRelativePath);
        $this->dropbox->call('move', $dropboxFrom, $dropboxTo);
        $this->dropbox->call('copy', $dropboxFrom, $dropboxTo);
        $this->dropbox->call('delete', $dropboxRelativePath);
        $this->dropbox->call('restore', $dropboxRelativePath, $rev);

        // Public links
        $this->dropbox->call('shares', $dropboxRelativePath);
        $this->dropbox->call('media', $dropboxRelativePath);

        // Synchronise
        $this->dropbox->call('delta', $cursor);
        $this->dropbox->call('synchronise', $localAbsolutePath);


More info
---------
https://www.dropbox.com/developers/reference/api