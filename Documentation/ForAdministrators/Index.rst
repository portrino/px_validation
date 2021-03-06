﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _for-administrators:

For Administrators
===================

Import
------

There are two ways of installing the extension. Click here for more details: `<https://wiki.typo3.org/Composer#Composer_Mode>`_

Import the extension to your server from the `TER <http://typo3.org/extensions/repository/view/px_validation>`_ or clone it from `GitHub <https://github.com/portrino/px_validation/>`_.

From TER (Classic Mode)
^^^^^^^^^^^^^^^^^^^^^^^

Select "*Get Extensions*" in the extension manager and update your extension list. Search for "px_validation" and click "Import and Install" to get the latest version.
There are no other dependencies than TYPO3 6.2.

From GitHub
^^^^^^^^^^^

Please use the following command to get the extension from GIT.

.. code-block:: bash

    git clone https://github.com/portrino/px_validation

Via Composer (Composer Mode)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Since TYPO3 7.x you are able to get extension via composer. As described on `<https://wiki.typo3.org/Composer#Composer_Mode>`_ you just have to use TYPO3 in Composer Mode
and add this line to your require section within the composer.json file and run ``composer install`` and ``composer update``.

.. code-block:: json

    "portrino/px_validation": "dev-master",

If you want a specific version than change "dev-master" to the version you need.

Install
-------

Wether you run your TYPO3 in Classic Mode or Composer Mode you should install the extension via ExtensionManager or via Composer. Click `here <https://wiki.typo3.org/Composer>`_ for more details

Configure
---------
Configure the extension as you wish after you have successfully installed it.

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   TypoScript/Index