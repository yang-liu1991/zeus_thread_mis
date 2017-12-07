<?php

namespace backend\models\record;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThEntityInfo;
use backend\models\user\AddUserForm;

class OpenApiModel{

    public function findAccountByEntity($user_id,$email){
    try{
        $transaction = Yii::$app->db->beginTransaction();
        $uid=$this->newUser($user_id,$email);
        $entity=ThEntityInfo::find()
        ->where(['id' => $_GET['entity_id'],'user_id'=>$uid])
        ->one();
        $posts = Yii::$app->db->createCommand('select `timezone_id`,`referral`,`status`,`reasons`
        from th_account_info 
        where `entity_id`=:entity_id
        and `user_id`=:user_id;')
        ->bindValue(':entity_id',$_GET['entity_id'])
        ->bindValue(':user_id',$uid)
        ->queryAll();
        $data=array(
            'entity'=>$entity,
            'account_apply_list'=>$posts,
            'user_id'=>$this->encrypt($uid)
        );
        $transaction->commit();
        return $this->sendJson(0,"",$data);
        } catch(\Exception $e) {
            $transaction->rollBack();
            return $this->sendJson(-1,$e->getMessage(),"");
        }
        }


    public function findFbAccountByUser ($user_id,$email) {
    try {
        $transaction = Yii::$app->db->beginTransaction();
        $id=$this->newUser($user_id,$email);
        $posts = Yii::$app->db->createCommand('select `th_account_info`.id as account_id,
        `th_account_info`.`created_at` as created_at,
        `th_account_info`.`timezone_id` as timezone_id,
        `th_account_info`.`fbaccount_name` as fbaccount_name,
        `th_account_info`.`status` as status,
        `th_account_info`.`fbaccount_id` as fbaccount_id, 
        `th_account_info`.`reasons` as reasons,
        `th_entity_info`.`id` as entity_id,
        `th_entity_info`.`name_zh` as name_zh
         from `th_account_info` inner join
        `th_entity_info` on (`th_account_info`.`entity_id`=`th_entity_info`.id)
        where `th_account_info`.`user_id`=:id')
        ->bindValue(':id',$id)
        ->queryAll();
        $result=array(
            "list"=>$posts,
            "user_id"=>$this->encrypt($id),
        );
        $transaction->commit();
        return $this->sendJson(0,"",$result);
    } catch(\Exception $e) {
        $transaction->rollBack();
       return $this->sendJson(-1,$e->getMessage(),"");
    }
    }

    public function accountSubmit($user_id,$email){
            if(Yii::$app->request->post('entity_id')==null){
                //如果entity_id  为空，那么new
                $result=$this->addNewAccount($user_id,$email);
            }else{
                $result=$this->updateAccount($user_id,$email);
                //如果 entity_id 不为空，那么update
            }
            return $result;
    }
    /**
     * 新建账户
     */
    private function addNewAccount($id,$email){
    try {
        $transaction = Yii::$app->db->beginTransaction();  
        $user_id=$this->newUser($id,$email);
        $times=time();
        $entity=new ThEntityInfo();
        $entity->user_id=$user_id;
        $entity->name_en=$_POST['name_en'];
        $entity->name_zh=$_POST['name_zh'];
        $entity->address_en=$_POST['address_en'];
        $entity->address_zh=$_POST['address_zh'];
        $entity->promotable_urls=$_POST['promotable_urls'];
        $entity->official_website_url=$_POST['official_website_url'];
        $entity->promotable_page_ids=Yii::$app->request->post('promotable_page_ids');
        $entity->promotable_app_ids=Yii::$app->request->post('promotable_app_ids');
        $entity->promotable_page_urls=Yii::$app->request->post('promotable_page_urls');
        $entity->vertical=$_POST['vertical'];
        $entity->subvertical=$_POST['subvertical'];
        $entity->is_smb=$_POST['is_smb'];
        $entity->payname=$_POST['payname'];
        $entity->contact=$_POST['contact'];
        $entity->business_registration=$_POST['business_registration'];
        $entity->business_registration_id=$_POST['business_registration_id'];
        $entity->advertiser_business_id=Yii::$app->request->post('advertiser_business_id');
        $entity->additional_comment=Yii::$app->request->post('additional_comment');
        $entity->comment=Yii::$app->request->post('comment');
        $entity->audit_status=0;
        $entity->created_at=$times;
        $entity->updated_at=$times;
        $entity->save();
        //查找出 推荐人id；
        $referral=Yii::$app->request->post('referral');
        $business = Yii::$app->db->createCommand('SELECT business_id as business_id ,company_id as company_id FROM 
        th_agency_business WHERE referral=:id')
        ->bindValue(':id',$referral)
        ->queryOne();
        if($business){
        $bussiness_id=$business['business_id'];
        $company_id=$business['company_id'];
        }else{
            throw new \Exception("未找到推荐人信息");
           // $transaction->rollBack();
           // return $this->sendJson(-1,'未发现推荐人信息',"");
        }

        // 插入数据库 account；
        $account_apply_list=Yii::$app->request->post('account_apply_list');
        $apply_list=json_decode($account_apply_list);
        $insert_data=array();
        $apply_list_length=count($apply_list);
        for($i=0;$i<$apply_list_length;$i++){
            $item=$apply_list[$i];
            for($j=0;$j<$item->number;$j++){
                $fbname=$this->newfbName($j,$i,$entity);
                $accountInfo=array(
                    $fbname,
                    $bussiness_id,
                    $user_id,
                    $company_id,
                    $entity->id,
                    $item->timezone_id,
                    $referral,
                    $times,
                    $times
                );
                array_push($insert_data,$accountInfo);
            }
        }

        Yii::$app->db->createCommand()->batchInsert('th_account_info', 
        ['fbaccount_name', 
        'business_agency_id',
        'user_id',
        'company_id',
        'entity_id',
        'timezone_id',
        'referral',
        'created_at',
        'updated_at'
        ], $insert_data)->execute();
        $data =array(
            'user_id'=>$this->encrypt($user_id)
        );
        $transaction->commit();
        return $this->sendJson(0,"",$data);
    }catch(\Exception $e) {
        $transaction->rollBack();
        return $this->sendJson(-1,$e->getMessage(),"");
    }
    }
    private function newfbName($j,$i,$entity){
        $sp='-';
        $str=$entity->name_en . "" . $sp . "" . $entity->id . "" . $sp . "" . $j. $sp . "" . $i;
        return $str;
    }

    /**
     * 更新账户信息
     */
    private function updateAccount($id,$email){
    try {
        $transaction = Yii::$app->db->beginTransaction();  
        $user_id=$this->newUser($id,$email);
        $times=time();
        $entity=ThEntityInfo::find()
        ->where(['id' => $_POST['entity_id'],'user_id'=>$user_id])
        ->one();
        $entity->user_id=$user_id;
        $entity->name_en=$_POST['name_en'];
        $entity->name_zh=$_POST['name_zh'];
        $entity->address_en=$_POST['address_en'];
        $entity->address_zh=$_POST['address_zh'];
        $entity->promotable_urls=$_POST['promotable_urls'];
        $entity->official_website_url=$_POST['official_website_url'];
        $entity->promotable_page_ids=Yii::$app->request->post('promotable_page_ids');
        $entity->promotable_app_ids=Yii::$app->request->post('promotable_app_ids');
        $entity->promotable_page_urls=Yii::$app->request->post('promotable_page_urls');
        $entity->vertical=$_POST['vertical'];
        $entity->subvertical=$_POST['subvertical'];
        $entity->is_smb=$_POST['is_smb'];
        $entity->payname=$_POST['payname'];
        $entity->contact=$_POST['contact'];
        $entity->audit_status=0;
        $entity->business_registration=$_POST['business_registration'];
        $entity->business_registration_id=$_POST['business_registration_id'];
        $entity->advertiser_business_id=Yii::$app->request->post('advertiser_business_id');
        $entity->additional_comment=Yii::$app->request->post('additional_comment');
        $entity->comment=Yii::$app->request->post('comment');
        $entity->updated_at=$times;
        $entity->save();
        //更新账户审核为 0；
        Yii::$app->db->createCommand('UPDATE th_account_info SET status=0 WHERE entity_id=:id and user_id=:user_id')
        ->bindValue(':id',$_POST['entity_id'])
        ->bindValue(':user_id',$user_id)
        ->execute();

        $data =array(
            'user_id'=>$this->encrypt($user_id)
        );
        $transaction->commit();
        return $this->sendJson(0,"",$data);

    }catch(Exception $e) {
        $transaction->rollBack();
        return $this->sendJson(-1,$e->getMessage(),"");
    }
    }

    /**
     * 新建立 user 信息
     */
    private function newUser($user_id,$email){
        if($user_id==0){
            $userModel= new AddUserForm();
            $userModel->setAttributes(array('email'=>$email,'password'=>$email,'repassword'=>$email));
            $user=$userModel->signup();
            $user_id=$user->id;
            return $user_id;
        }
       
        return $this->decrypt($user_id);
    }
    /**
     * 解密函数
     */
    private function decrypt($info){
        if(Yii::$app->request->isPost){
            $name=Yii::$app->request->post('project_name'); 
        }else{
            $name=Yii::$app->request->get('project_name'); 
        }        
        $key='ilovephp'.'-'.$name;
        $msg=openssl_decrypt ($info, 'des-ecb', $key);
        $deinfo=json_decode($msg);
        $user_id=$deinfo->{'user_id'};
        $project_name=$deinfo->{'project_name'};
        //验证完整性。md5 
        $md5_msg=$deinfo->{'info'};
        $real_md5_msg=md5($user_id.'-'.$project_name.'k');
        //如果名字和 信息摘要相等，那么证明密文完整性没有被破坏
        if($name==$project_name&&$md5_msg==$real_md5_msg){
            return $user_id;
        }else{
            throw new \Exception("验证未通过");
        }
    }
    /**
     * 加密函数
     */
    private function encrypt($user_id){
        if(Yii::$app->request->isPost){
        $name=Yii::$app->request->post('project_name'); 
        }else{
        $name=Yii::$app->request->get('project_name'); 
        }       
        $key='ilovephp'.'-'.$name;
        $real_md5_msg=md5($user_id.'-'.$name.'k');
        $json=array(
            'user_id'=>$user_id,
            'project_name'=>$name,
            'info'=>$real_md5_msg,
        );
        $data=json_encode($json);
        return openssl_encrypt ($data, 'des-ecb', $key);
    }
    private function sendJson($code,$msg,$data){
        return array(
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data,
        );
    }

}