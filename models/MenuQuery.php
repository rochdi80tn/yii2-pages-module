<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use yii\db\ActiveQuery;
use creocoder\nestedsets\NestedSetsQueryBehavior;

/**
 * Class MenuQuery
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class MenuQuery extends ActiveQuery
{
    /**
     * Query behaviors
     * @return array
     */
    public function behaviors()
    {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}