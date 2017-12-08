<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-04-14 14:00:03
 */

namespace backend\models\creatives;

use Yii;
use yii\base\Model;
use yii\base\Exception;
use common\models\Conversion;
use common\models\DownloadExcel;
use backend\models\ThreadBaseModel;


class CreativesUploadModel extends ThreadBaseModel
{
    public $upload_file;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['upload_file', 'file']
        ];
    }
}