<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages;

use dmstr\modules\pages\models\Tree;
use yii\filters\AccessControl;

/**
 * Class Module
 * @package dmstr\modules\pages
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Module extends \yii\base\Module
{
    /**
     * @var array the list of rights that are allowed to access this module.
     * If you modify, you also need to enable authManager.
     * http://www.yiiframework.com/doc-2.0/guide-security-authorization.html
     */
    public $roles = [];

    public $pagesWithChildrenHasUrl = false;

    public $availableRoutes = [
        '/pages/default/page' => '/pages/default/page',
        '/site/index' => '/site/index',
    ];
    public $availableViews = [
        '@vendor/dmstr/yii2-widgets-module/example-views/default.php' => 'Default',
        '@vendor/dmstr/yii2-widgets-module/example-views/column1.php' => 'One Column (with container)'
    ];


    /**
     * Restrict access permissions to admin user and users with auth-item 'module-controller'
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            if ($this->roles) {
                                foreach ($this->roles as $role) {
                                    if (\Yii::$app->user->can($role)) {
                                        return true;
                                    }
                                }
                                return (\Yii::$app->user->identity && \Yii::$app->user->identity->isAdmin);
                            }
                            return true;
                        },
                    ]
                ]
            ]
        ];
    }

    public function getLocalizedRootNode()
    {
        $localizedRoot = 'root_' . \Yii::$app->language;
        \Yii::trace('localizedRoot: ' . $localizedRoot, __METHOD__);
        $page = Tree::findOne(
            [
                Tree::ATTR_NAME_ID => $localizedRoot,
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
                Tree::ATTR_VISIBLE => Tree::VISIBLE
            ]
        );
        return $page;
    }
}
