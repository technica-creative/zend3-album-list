<?php

namespace Album;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;

/*
The config information is passed to the relevant components by the ServiceManager. We need two initial sections: controllers and view_manager. The controllers section provides a list of all the controllers provided by the module. We will need one controller, AlbumController; we'll reference it by its fully qualified class name, and use the zend-servicemanager InvokableFactory to create instances of it.

Within the view_manager section, we add our view directory to the TemplatePathStack configuration. This will allow it to find the view scripts for the Album module that are stored in our view/ directory.
*/
return [
    
    /*
    The name of the route is ‘album’ and has a type of ‘segment’. The segment route allows us to specify placeholders in the URL pattern (route) that will be mapped to named parameters in the matched route. In this case, the route is /album[/:action[/:id]] which will match any URL that starts with /album. The next segment will be an optional action name, and then finally the next segment will be mapped to an optional id. The square brackets indicate that a segment is optional. The constraints section allows us to ensure that the characters within a segment are as expected, so we have limited actions to starting with a letter and then subsequent characters only being alphanumeric, underscore, or hyphen. We also limit the id to digits.

    The following routes are produced by this:
    /album
    /album/add
    /album/edit/2
    /album/delete/4
    */
    'router' => [
        'routes' => [
            'album' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/album[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AlbumController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'album' => __DIR__ . '/../view',
        ],
    ],
];
