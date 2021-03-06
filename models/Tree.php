<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the tree model class, extended from \kartik\tree\models\Tree
 *
 * @property string  $page_title
 * @property string  $name_id
 * @property string  $slug
 * @property string  $route
 * @property string  $view
 * @property string  $default_meta_keywords
 * @property string  $default_meta_description
 * @property string  $request_params
 * @property integer $access_owner
 * @property string  $access_domain
 * @property string  $access_read
 * @property string  $access_update
 * @property string  $access_delete
 * @property string  $created_at
 * @property string  $updated_at
 *
 */
class Tree extends \kartik\tree\models\Tree
{
    /**
     * Constants useful for frontend actions
     */
    const ICON_TYPE_CSS = 1;
    const ICON_TYPE_RAW = 2;

    const ACTIVE = 1;
    const NOT_ACTIVE = 0;

    const SELECTED = 1;
    const NOT_SELECTED = 0;

    const DISABLED = 1;
    const NOT_DISABLED = 0;

    const READ_ONLY = 1;
    const NOT_READ_ONLY = 0;

    const VISIBLE = 1;
    const NOT_VISIBLE = 0;


    const COLLAPSED = 1;
    const NOT_COLLAPSED = 0;


    /**
     * Attribute names
     */
    const ATTR_ID = 'id';
    const ATTR_NAME_ID = 'name_id';
    const ATTR_ACCESS_DOMAIN = 'access_domain';
    const ATTR_ROUTE = 'route';
    const ATTR_VIEW = 'view';
    const ATTR_REQUEST_PARAMS = 'request_params';
    const ATTR_ICON = 'icon';
    const ATTR_ICON_TYPE = 'icon_type';
    const ATTR_ACTIVE = 'active';
    const ATTR_SELECTED = 'selected';
    const ATTR_DISABLED = 'disabled';
    const ATTR_READ_ONLY = 'readonly';
    const ATTR_VISIBLE = 'visible';
    const ATTR_COLLAPSED = 'collapsed';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmstr_page';
    }

    /**
     * @inheritdoc
     *
     * Use yii\behaviors\TimestampBehavior for created_at and updated_at attribute
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                [
                    'class'              => TimestampBehavior::className(),
                    'createdAtAttribute' => 'created_at',
                    'updatedAtAttribute' => 'updated_at',
                    'value'              => new Expression('NOW()'),
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [
                    [
                        'name_id',
                    ],
                    'unique'
                ],
                [
                    [
                        'name_id',
                    ],
                    'required'
                ],
                [
                    [
                        'name_id',
                        'page_title',
                        'slug',
                        'route',
                        'view',
                        'default_meta_keywords',
                        'request_params',
                        'access_read',
                        'access_update',
                        'access_delete',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'default_meta_description',
                    ],
                    'string',
                    'max' => 160
                ],
                [
                    [
                        'access_domain',
                    ],
                    'string',
                    'max' => 8
                ],
                [
                    [
                        'access_owner',
                    ],
                    'integer',
                    'max' => 11
                ],
                [
                    [
                        'name_id',
                        'page_title',
                        'slug',
                        'route',
                        'view',
                        'default_meta_keywords',
                        'default_meta_description',
                        'request_params',
                        'access_domain',
                        'access_owner',
                        'access_read',
                        'access_update',
                        'access_delete',
                        'created_at',
                        'updated_at',
                    ],
                    'safe'
                ],
            ]
        );
    }

    /**
     * Override isDisabled method if you need as shown in the
     * example below. You can override similarly other methods
     * like isActive, isMovable etc.
     */
    public function isDisabled()
    {
        //if (Yii::$app->user->id !== 'admin') {
        //return true;
        //}

        return parent::isDisabled();
    }

    /**
     * @return array
     */
    public static function optsAccessDomain()
    {
        $availableLanguages[Yii::$app->language] = Yii::$app->language;
        return $availableLanguages;
    }

    /**
     * Get all configured
     * @return array list of options
     */
    public static function optsView()
    {
        return \Yii::$app->getModule('pages')->availableViews;
    }

    /**
     * TODO which routes will be provided by default ?
     *
     * @return array
     */
    public static function optsRoute()
    {
        return \Yii::$app->getModule('pages')->availableRoutes;
    }

    /**
     * @param array $additionalParams
     *
     * @return null|string
     */
    public function createUrl($additionalParams = [])
    {
        $leave = Tree::find()->where(['id' => $this->id])->one();

        if ($leave === null) {
            Yii::error("Tree node with id=" . $this->id . " not found.");
            return null;
        }

        // TODO merged request and additional params, URL rule has therefore to be updated/extended
        if ($leave->route !== null) {

            if ($additionalParams) {
                // merge with $params
            }
            return self::getSluggedUrl($leave);
        } elseif ($leave->route !== null) {
            return \Yii::$app->urlManager->createUrl([$leave->route]);
        }
    }

    /**
     * Check if a tree route and view are set
     *
     * @return bool
     */
    public function hasRoute()
    {
        if (!empty($this->route) && !empty($this->view)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get relative url from tree leave
     *
     * @param $leave
     *
     * @return null|string
     */
    public static function getSluggedUrl($leave)
    {
	    if (
		    (
			    !isset(\Yii::$app->modules['pages']) ||
			    (is_array(\Yii::$app->modules['pages']) && (!isset(\Yii::$app->modules['pages']['pagesWithChildrenHasUrl']) || !\Yii::$app->modules['pages']['pagesWithChildrenHasUrl'])) ||
			    (is_object(\Yii::$app->modules['pages']) && !\Yii::$app->modules['pages']->pagesWithChildrenHasUrl)
	        ) &&
		    $leave->children(1)->one())
	    {
	        // pages with children do not get an URL
            return null;
	    }

        if ($leave->route) {

            // TODO provide all parents in URL
            // provide first parent for URL creation
            $parent      = $leave->parents(1)->one();
            $parentLeave = false;

            if ($parent) {
                if ($parent->lvl != '0') {
                    $parentLeave = Inflector::slug($parent->name);
                }
            }
            return Url::toRoute(
                [
                    $leave->route,
                    'id'          => $leave->id,
                    'pageName'    => Inflector::slug($leave->page_title),
                    'parentLeave' => $parentLeave,
                ]
            );
        } else {
            return null;
        }
    }

    /**
     * @return active and visible menu nodes for the current application language
     *
     * @param $rootName the name of the root node
     *
     * @return array
     */
    public static function getMenuItems($rootName)
    {
        // Get root node by name
        $rootCondition['name_id'] = $rootName;
        if (!Yii::$app->user->can('pages')) {
            $rootCondition[Tree::ATTR_DISABLED] = Tree::NOT_DISABLED;
        }
        $rootNode = self::findOne($rootCondition);

        if ($rootNode === null) {
            return [];
        }

	    /**
	     * @var $leaves Tree[]
	     */

        // Get all leaves from this root node
        $leavesQuery = $rootNode->children()->andWhere(
            [
                Tree::ATTR_ACTIVE        => Tree::ACTIVE,
                Tree::ATTR_VISIBLE       => Tree::VISIBLE,
                Tree::ATTR_ACCESS_DOMAIN => \Yii::$app->language,
            ]
        );
        if (!Yii::$app->user->can('pages')) {
            $leavesQuery->andWhere(
                [
                    Tree::ATTR_DISABLED => Tree::NOT_DISABLED,
                ]
            );
        }

        $leaves = $leavesQuery->all();

        if ($leaves === null) {
            return [];
        }

        // tree mapping and leave stack
        $treeMap = [];
        $stack   = [];

        if (count($leaves) > 0) {

            foreach ($leaves as $page) {

                // prepare node identifiers
                $pageOptions = [
                    'data-page-id' => $page->id,
                    'data-lvl'     => $page->lvl,
                ];

                $itemTemplate = [
                    'label'       => ($page->icon) ? '<i class="' . $page->icon . '"></i> ' . $page->name : $page->name,
                    'url'         => $page->createUrl(),
                    'linkOptions' => $pageOptions,
                ];
                $item         = $itemTemplate;

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['linkOptions']['data-lvl'] >= $item['linkOptions']['data-lvl']) {
                    array_pop($stack);
                    $counter--;
                }

                // Stack is now empty (check root again)
                if ($counter == 0) {
                    // assign root node
                    $i           = count($treeMap);
                    $treeMap[$i] = $item;
                    $stack[]     = &$treeMap[$i];
                } else {
                    if (!isset($stack[$counter - 1]['items'])) {
                        $stack[$counter - 1]['items'] = [];
                    }
                    // add the node to parent node
                    $i                                = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[]                          = &$stack[$counter - 1]['items'][$i];
                }
            }
        }
        return array_filter($treeMap);
    }
}
