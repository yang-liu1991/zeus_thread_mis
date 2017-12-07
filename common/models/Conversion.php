<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-21 16:26:14
 */

namespace common\models;

use Yii;
use yii\base\Model;
use backend\models\record\ThAgencyBusinessSearch;


class Conversion extends Model 
{
	/**
	 *	索引数组转化
	 */
	public static function indexToAssociative($array) 
	{
		$newArray = [];
		$idCount = count($array);
		for($i=0; $i<$idCount; $i++)
		{
			array_push($newArray, $array[$i]);
		}

		return $newArray;
	}

	/**
	 *	串行转字符串
	 */
	public static function getPromotableUrls($data, $type)
	{
		$websiteAll = json_decode($data, true);
		switch($type)
		{
			case "normal":
				$websiteArray = $websiteAll['normal'];
				break;
			case "abnormal":
				$websiteArray = $websiteAll['abnormal'];
				break;
			case "all":
				$normal = $websiteAll['normal'] ? $websiteAll['normal'] : [];
				$abnormal = $websiteAll['abnormal'] ? $websiteAll['abnormal'] : [];
				$websiteArray = array_unique(array_merge($normal, $abnormal));
				break;
		}	
		return implode("</br>", $websiteArray);
	}


	/**
	 *	转义promotable_page_ids
	 */
	public static function getPromotablePageIds($promotable_page_ids)
	{
		if(!$promotable_page_ids) return '';
		$promotablePageIdsArr = json_decode($promotable_page_ids);
		$promotablePageIds = implode(",", $promotablePageIdsArr);
		$promotablePageIds	= str_replace(" ", "", $promotablePageIds);
		return $promotablePageIds;
	}
	
	/**
	 *	转义promotable_page_urls
	 */
	public static function getPromotablePageUrls($promotable_page_urls)
	{
		if(!$promotable_page_urls) return '';
		$promotablePageUrlsArr = json_decode($promotable_page_urls);
		$promotablePageUrls = implode("<br/>", $promotablePageUrlsArr);
		$promotablePageUrls	= str_replace(" ", "", $promotablePageUrls);
		return $promotablePageUrls;
	}

	/**
	 *	转义promotable_app_ids
	 */
	public static function getPromotableAppIds($promotable_app_ids)
	{
		if(!$promotable_app_ids) return '';
		$promotableAppIdsArr = json_decode($promotable_app_ids);
		$promotableAppIds = implode(",", $promotableAppIdsArr);
		$promotableAppIds	= str_replace(" ", "", $promotableAppIds);
		return $promotableAppIds;
	}


	/**
	 *	审核状态判断
	 */
	public static function getAuditStatus($auditStatus)
	{
		switch($auditStatus)
		{
			case 0:
				return sprintf('<span style="width:80px;" class="btn btn-xs btn-warning">%s</span>', '等待FB审核');;
			case 1:
                return sprintf('<span style="width:80px;" class="btn btn-xs btn-success">%s</span>', '审核通过');
			case 2:
                return sprintf('<span style="width:80px;" class="btn btn-xs btn-danger">%s</span>', '审核失败');
            default:
                return sprintf('<span style="width:80px;" class="btn btn-xs btn-info">%s</span>', '未知状态');
		}
	}
	
	/**
	 *  Admin侧显示为详细的状态
	 *	开户状态判断
	 */
	public static function getAccountStatus($accountStatus)
	{
		switch($accountStatus)
		{
			case 0:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-warning">%s</span>', 'WAITING');
                break;
			case 1:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-info">%s</span>', 'PENDING');
				break;
            case 2:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-primary">%s</span>', 'UNDERREVIEW');
                break;	
            case 3:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-success">%s</span>', 'APPROVED');
                break;	
            case 4:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'RE_CHANGE');
                break;	
            case 5:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'DISAPPROVED');
                break;	
            case 6:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'CANCELLED');
                break;	
			case 7:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'ABNORMAL');
				break;
			case 8:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'FORCEOUT');
				break;
			default:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', $accountStatus);
                break;	
		}
	}
	

	/**
	 *	广告主部分状态显示为Facebook开户
	 */
	public static function getAdAccountStatus($accountStatus)
	{
		switch($accountStatus)
		{
			case 0:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-warning">%s</span>', 'BLUEFOCUS');
                break;
			case 1:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-info">%s</span>', 'FACEBOOK');
				break;
            case 2:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-info">%s</span>', 'FACEBOOK');
                break;	
            case 3:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-success">%s</span>', 'APPROVED');
                break;	
            case 4:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'RE_CHANGE');
                break;	
            case 5:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'DISAPPROVED');
                break;	
            case 6:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', 'CANCELLED');
                break;	
			default:
                return sprintf('<span style="width:90px;" class="btn btn-xs btn-danger">%s</span>', $accountStatus);
                break;	
		}
	}


	public static function getRemind($accountStatus)
	{
		switch($accountStatus)
		{
			case 0: return 'Remind BlueFocus';break;	
			case 1:	return 'Remind FaceBook';break;
			case 2:	return 'Remind FaceBook';break;
		}
	}


	/**
	 *	英文地址转化处理
	 */
	public static function getAddressEn($address_en)
	{
		if($address_en)
		{
			$addressEnObj	= json_decode($address_en, true);
			$addressStr = '';
			foreach($addressEnObj as $key => $value)
			{
				$addressStr .= sprintf("%s\t:\t%s %s", $key, $value, "<br>");
			}
			return $addressStr;
		}
		return $address_en;
	}


	/**
	 *	返回是否为SMB客户
	 */
	public static function getIsSmb($is_smb)
	{
		switch($is_smb)
		{
			case 0:
				return '否';break;
			case 1:
				return '是';break;
			default:
				return '未知';break;
		}
	}

	/**
	 *	返回备注信息
	 */
	public static function getComment($comment)
	{
		if($comment)
			return implode("<br>", json_decode($comment, true));
		return '';
	}

	/**
	 *	AE备注状态
	 */
	public static function getNoteStatus($noteStatus)
	{
		if($noteStatus)
		{
			return 'AE备注';
		}
		return '无';
	}

	/**
	 *	操作类型
	 */
	public static function getActionType($created_at, $updated_at)
	{
		if($created_at == $updated_at)
		{
			return '注册';
		}
		return '变更';
	}

	/**
	 *	Account Id
	 */
	public static function getAccountId($fbaccount_id)
	{
		if(!$fbaccount_id)
		{
			return '<input type="text" name="fbaccount_id" id="fbaccount_id_form" />';
		}
		return $fbaccount_id;
	}

	/**
	 *	获取代理公司
	 */
	public static function getCompany($company_id)
	{
		$bussiness_obj = ThAgencyBusinessSearch::getOneBusinessByCompanyId($company_id);
		if($bussiness_obj) return $bussiness_obj->business_name;
		return '';
	}

	public static function arrayMap($arr, $key)
	{
		if(isset($arr[$key]))
			return $arr[$key];
		else
			return $key;
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
