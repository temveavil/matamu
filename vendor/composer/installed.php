<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'matomo/device-detector' => array(
            'pretty_version' => '6.5.2',
            'version' => '6.5.2.0',
            'reference' => 'e83ce7a97f05d8ee3e1eee7ea77a73b3bdfebd9a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../matomo/device-detector',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'mustangostang/spyc' => array(
            'pretty_version' => '0.6.3',
            'version' => '0.6.3.0',
            'reference' => '4627c838b16550b666d15aeae1e5289dd5b77da0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../mustangostang/spyc',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'piwik/device-detector' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '6.5.2',
            ),
        ),
    ),
);
