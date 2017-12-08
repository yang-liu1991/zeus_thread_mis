<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-08-25 18:24:03
 */

namespace backend\models\account;

use Yii;


class FbVertical
{
	/**
	 *	获取所有垂直业务线
	 */
	static public function getVerticals()
	{
		return array_keys(self::getVerticalMappings());
	}

	/**
	 *	获取垂直业务线下面的所有子业务线
	 *	@params	$str $vertical
	 *	@return array
	 */
	static public function getSubVerticals($vertical)
	{
		return !empty($vertical) ? self::getVerticalMappings()[$vertical] : '';
	}

	/**
	 *	获取垂直业务线的索引值
	 *	@params	$str $vertical
	 */
	static public function getVerticalsIndex($vertical)
	{
		return !empty($vertical) ? array_search($vertical, array_keys(self::getVerticalMappings())) : '';
	}

	/**
	 *	获取重直业务线下面子业务线的索引
	 *	@params	$str
	 */
	static public function getSubVerticalIndex($vertical, $subvertical)
	{
		if(!$vertical || !$subvertical) return '';
		return array_search($subvertical, self::getSubVerticals($vertical));
	}

	/**
	 *
	 */
	static public function getSubVerticalsByIndex($verticalId)
	{
		$verticals = array_keys(self::getVerticalMappings());
		return self::getVerticalMappings()[$verticals[$verticalId]];
	}

	/**
	 *	子业务线映射
	 */
	static public function getVerticalMappings()
	{
		return [
			"ADVERTISING_AND_MARKETING"	=> [
				"PR",
				"MEDIA",
				"DIGITAL_ADVERTISING_AND_MARKETING_OR_UNTAGGED_AGENCIES"
			],
			"CONSUMER_PACKAGED_GOODS" => [
				"PHARMACEUTICAL_OR_HEALTH",
				"TOBACCO",
				"WATER_AND_SOFT_DRINK_AND_BAVERAGE",
				"BEAUTY_AND_PERSONAL_CARE",
				"FOOD",
				"PET",
				"BEER_AND_WINE_AND_LIQUOR",
				"OFFICE",
				"HOUSEHOLD_GOODS",
			],
			"ECOMMERCE"	=> [
				"SMB_CATALOG",
				"DAILYDEALS",
				"AUCTIONS",
				"APPAREL_AND_ACCESSORIES"
			],
			"EDUCATION"	=> [
				"ED_TECH",
				"NOT_FOR_PROFIT_COLLEGES_AND_UNIVERSITIES",
				"TRADE_SCHOOL",
				"SCHOOL_AND_EARLY_CHILDREN_EDCATION",
				"EDUCATION_RESOURCES",
				"FOR_PROFIT_COLLEGES_AND_UNIVERSITIES",
				"ELEARNING_AND_MASSIVE_ONLINE_OPEN_COURSES"
			],
			"ENERGY_AND_UTILITIES" => [
				"SMB_ENERGY",
				"UTILITIES_AND_ENERGY_EQUIPMENT_AND_SERVICES",
				"UTILITIES_AND_ENERGY_EQUIPMENT_AND_SERVICES"
			],
			"ENTERTAINMENT_AND_MEDIA" => [
				"STREAMING",
				"SMB_INFORMATION",
				"GAMBLING",
				"MOVIES",
				"SMB_ARTISTS_AND_PERFORMERS",
				"PUBLISHING_INTERNET",
				"ARTS",
				"SPORTS",
				"EVENTS",
				"FITNESS",
				"MUSIC_AND_RADIO",
				"MUSEUMS_AND_PARKS_AND_LIBRARIES",
				"SMB_AGENTS_AND_PROMOTERS",
				"TELEVISION"
			],
			"FINANCIAL_SERVICES" => [
				"RETAIL_AND_CREDIT_UNION_AND_COMMERCIAL_BANK",
				"INSURANCE",
				"REAL_ESTATE",
				"INVESTMENT_BANK_AND_BROKERAGE",
				"CREDIT_AND_FINANCING_AND_MORTAGES"
			],
			"GAMING" => [
				"ONLINE_OR_SOFTWARE",
				"CONSOLE_DEVELOPERCONSOLE_DEVELOPER",
				"SOFTWARE",
				"SMB_CROSS_PLATFORM",
				"SMB_CANVAS",
				"SMB_GAME_AND_TOY",
				"REAL_MONEY_OR_SKILLED_GAMING",
				"CONSOLE_DEVICE",
				"MOBILE_AND_SOCIAL"
			],
			"GOVERMENT_AND_POLITICS" => [
				"SEASONAL_POLITICAL_SPENDERS",
				"GOVERNMENT",
				"POLITICAL"
			],
			"ORGANIZATIONS_AND_ASSOCIATIONS" => [
				"SMB_RELIGIOUS",
				"NON_PROFIT",
				"RELIGIOUS"
			],
			"OTHER" => [
				"B2B_MANUFACTURING",
				"CONSTRUCTION_AND_MINING",
				"TRANSPORTATION_EQUIPMENT",
				"ECOMMERCE_AGRICULTURE_AND_FARMING"
			],
			"PROFESSIONAL_SERVICES" => [
				"CONSULTING",
				"FAMILY_AND_HEALTH",
				"BUSINESS_SUPPORT_SERVICES",
				"HOME_SERVICE",
				"DATING",
				"ACCOUNTING_AND_TAXES_AND_LEGAL",
				"PHOTOGRAPHY_AND_FILMING_SERVICES",
				"SMB_PERSONAL_CARE",
				"ENGINEERING_AND_DESIGN",
				"CAREER",
				"SMB_REPAIR_AND_MAINTENANCE"
			],
			"RETAIL" => [
				"BOOKSTORES",
				"GROCERY_AND_DRUG_AND_CONVENIENCE",
				"SMB_OTHER",
				"HOME_AND_OFFICE",
				"HOME_IMPROVEMENT",
				"RESTAURANT",
				"PET_RETAIL",
				"SMB_RENTALS",
				"FOOTWEAR",
				"DEPARTMENT_STORE",
				"SPORTING",
				"TOY_AND_HOBBY",
				"APPAREL_AND_ACCESSORIES",
				"SMB_ELECTRONICS_AND_APPLIANCES"
			],
			"TECHNOLOGY" => [
				"SMB_NAVIGATION_AND_MEASUREMENT",
				"B2B",
				"COMPUTING_AND_PERIPHERALS",
				"CONSUMER_TECH",
				"DESKTOP_SOFTWARE",
				"SMB_CONSUMER_MOBILE_DEVICE",
				"CONSUMER_ELECTRONICS",
				"MOBILE_APPS",
				"ECOMMERCE_ECATALOG"
			],
			"TELECOM" => [
				"SMB_WIRELINE_SERVICES",
				"WIRELESS_SERVICES",
				"CABLE_AND_SATELLITE"
			],
			"TRAVEL" => [
				"RAILROADS",
				"HOTEL_AND_ACCOMODATION",
				"TRUCK_AND_MOVING",
				"AIR_FREIGHT_OR_PACKAGE",
				"BUS_AND_TAXI_AND_AUTO_RETAL",
				"AIR",
				"CVB_CONVENTION_AND_VISITORS_BUREAU",
				"AUTO_RENTAL",
				"HIGHWAYS",
				"TRAVAL_AGENCY",
				"SMB_OPERATIONS_AND_OTHER",
				"CRUISES_AND_MARINE"
			],
			"RECREATIONAL" => [
				"SMB_MOTORCYCLE",
				"RV"
			]
		];	
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
