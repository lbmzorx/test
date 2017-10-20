<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/12
 * Time: 11:27
 */

namespace common\controllers;

use common\models\datamodels\Area;
use common\models\tools\Captcha;
use common\models\safe\MailMsgNotice;
use yii\helpers\Url;
use yii\web\Controller;
use common\models\safe\MobileMsgNotice;

class BaseController extends Controller
{
    public $error_url;

    //上传
    protected $upload_name;
    protected $upload_path;

    public function init(){
        parent::init();
    }

    protected function goErrUrl($url=null,$remove_session=true){
        if(!$url){
            $url=$this->error_url?$this->error_url:Url::to(['/']);
        }
        if($remove_session===true){
            \yii::$app->session->removeAll();
        }elseif ($remove_session!==false){
            if(is_array($remove_session)){
                foreach ($remove_session as $session_key){
                    \yii::$app->session->remove($session_key);
                }
            }else{
                \yii::$app->session->remove($remove_session);
            }
        }
        exit('<script>top.location.href="'.$url.'"</script>');
    }

    public function actionErrors(){
        return $this->renderPartial('/errors/index');
    }
    /**
     * 图形验证码
     * @return mixed
     */
    public function actionVerifyCode(){
        $captcha = new Captcha(['length'=>4]);
        return $captcha->entry('verify_code');
    }

    protected function re_json(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    protected function send_mail_verify($type,$mail){
        $cptcha=new Captcha();
        if(!$cptcha->check(\Yii::$app->request->post('verify_code'),'verify_code')){
            return ['status'=>false,'msg'=>'图形验证码错误'];
        }
        $mail_msg=new MailMsgNotice();
        return $mail_msg->send_mail_verify_code($type,$mail);
    }

    protected function send_mobile_verify($type,$mobile){
        $cptcha=new Captcha();
        if(!$cptcha->check(\Yii::$app->request->post('verify_code'),'verify_code')){
            return ['status'=>false,'msg'=>'图形验证码错误'];
        }
        $mobile_msg=new MobileMsgNotice();
        return $mobile_msg->send_verify_code($type,$mobile);
    }

    public function actionSendUserMailVerify(){
        self::re_json();
        $type=\Yii::$app->request->post('type');
        if(!$type){
            return ['status'=>false,'msg'=>'未指定类型'];
        }
        if(!isset(MobileMsgNotice::$type_key[$type])){
            $type= MobileMsgNotice::get_type_key($type);
        }
        $mail=$this->user['mail'];
        if(!$mail){
            return ['status'=>false,'msg'=>'EMAIL不正确'];
        }
        return self::send_mail_verify($type,$mail);
    }

    public function actionSendMailVerify(){
        self::re_json();
        $mail=\Yii::$app->request->post('mail');
        $type=\Yii::$app->request->post('type');
        if(!$type){
            return ['status'=>false,'msg'=>'未指定类型'];
        }
        if(!isset(MobileMsgNotice::$type_key[$type])){
            $type= MobileMsgNotice::get_type_key($type);
        }
        if(!$mail){
            return ['status'=>false,'msg'=>'EMAIL不正确'];
        }
        return self::send_mail_verify($type,$mail);
    }

    public function actionSendUserMobileVerify(){
        self::re_json();
        $type=\Yii::$app->request->post('type');
        $user_name=\Yii::$app->request->post('user_name');
        if(!$type){
            return ['status'=>false,'msg'=>'未指定类型'];
        }

        if($user_name){
            $user=\common\models\common\user\Users::find()->where(['or',['user_name'=>$user_name],
                ['mobile'=>$user_name],['mail'=>$user_name]])->asArray()->one();
            if(!$user['bind_mobile']){
                return ['status'=>false,'msg'=>'没有指定发送目标!'];
            }
            $mobile=$user['bind_mobile'];
        }else{
            if(!isset($this->user) || !$this->user){
                return ['status'=>false,'msg'=>'没有指定发送目标!!'];
            }
            $mobile=$this->user['bind_mobile'];
        }

        if(!$mobile || !is_numeric($mobile)){
            return ['status'=>false,'msg'=>'手机号不正确'];
        }
        if(!isset(MobileMsgNotice::$type_key[$type])){
            $type= MobileMsgNotice::get_type_key($type);
        }
        return  self::send_mobile_verify($type,$mobile);
    }

    public function actionSendMobileVerify(){
        self::re_json();
        $mobile=\Yii::$app->request->post('mobile');
        $type=\Yii::$app->request->post('type');
        if(!$type){
            return ['status'=>false,'msg'=>'未指定类型'];
        }
        if(!isset(MobileMsgNotice::$type_key[$type])){
            $type= MobileMsgNotice::get_type_key($type);
        }
        if(!$mobile || !is_numeric($mobile)){
            return ['status'=>false,'msg'=>'手机号不正确'];
        }
        return self::send_mobile_verify($type,$mobile);
    }


    public function actionGetArea()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $area = Area::find()->where(['parentid' => \Yii::$app->request->get('id')])->all();
        return ['status'=>true,'data'=>$area];
    }
    public function actionOut(){
        \yii::$app->user->logout();
        return $this->goHome();
//        $this->goErrUrl(\Yii::$app->request->getReferrer(),'user');
      //  \Yii::$app->session->set('user',null);
       // \Yii::$app->response->redirect(\yii\helpers\Url::to($url), 301)->send();
    }

    protected function first_big($sting,$prefix='_'){
        $b='';$a=explode($prefix,$sting);
        foreach ($a as $v){$b=$b.ucfirst($v);}
        return $b;
    }

    /**
     * 格式化图片上传流程
     * @param $name
     * @param $path
     * @return array
     */
    protected function uploadimg($name,$path,$isRand_params=true){
        $model = new \common\models\tools\UploadImg();
        $model->path='uploads/'.$path;
        $key = 'fo54_-298IOf?%h0g_h88ha';  //随便写的加密key;
        $model->name=md5($name.$key);
        if (\Yii::$app->request->isPost) {
            $model->imageFile =\yii\web\UploadedFile::getInstance($model, 'imageFile');
            if (!$model->upload()) {
                return ['status'=>false,'msg'=>$model->getErrors()];
            }
            if($isRand_params==true){
                $url=$model->img_service.'/'.$model->out_name.'?'.microtime(true);
            }else{
                $url=$model->img_service.'/'.$model->out_name;
            }
            return ['status'=>true,'url'=>$url];
        }
    }

    public function actionUpload()
    {
        self::re_json();
        $name=$this->uplaod_name.$this->upload_path;
        $get=\yii::$app->request->get();
        if($get){
            $name.=serialize($get);
        }
        return parent::uploadimg($name,$this->upload_path);
    }

    /**
     * 设置分页
     * @param $count
     * @return \common\tool\Page
     */
    public function setPage($count){
        $pages = new \common\tool\Page(['totalCount' => $count]);
        $pages->pageSizeLimit[1]=self::MAX_PAGE_SIZI;
        $page_size=\Yii::$app->request->get('page_size');
        if($page_size>self::MAX_PAGE_SIZI){
            $page_size=self::MAX_PAGE_SIZI;
        }elseif($page_size==''){
            $page_size=self::DEFAULT_PAGE_SIZI;
        }
        $pages->pageSize=$page_size;
        return $pages;
    }

    /**
     * 限制时间
     * @param $time_int
     * @return array
     */
    public function limit_time($time_int){
        return \common\tool\TimeLimit::data_range_time($time_int,$this->time_range_max,1,$this->time_bottom);
    }

    /**
     * 限制时间
     * @param array $time_sting
     * @param int $primary
     */
    public function limit_range(array $time_sting,$primary=1){
        $time_int[0]=\common\tool\TimeLimit::limit_bottom($time_sting[0],$this->time_bottom,time()-1);
        $time_int[1]=\common\tool\TimeLimit::limit_bottom($time_sting[1],$this->time_bottom,time());
        return \common\tool\TimeLimit::range_limit($time_int,$this->time_range_max,$primary,$this->time_bottom);
    }

    /**
     * 对时间进行限制
     * @return false|string
     */
    public function actionTimeRange(){
        $request=\yii::$app->request;
        $this->re_json();
        $time_select=$request->get('time_select');
        $time_type=$request->get('time_type');
        if($time_select==''){
            return ['data'=>date('Y-m-d H:i:s',$this->time_bottom)];
        }
        if($time_type=='min'){
            $time_min=strtotime($time_select.' '.$this->time_range_min);
            if($time_min<$this->time_bottom){
                return ['data'=>date('Y-m-d H:i:s',$this->time_bottom)];
            }else{
                return ['data'=>date('Y-m-d H:i:s',$time_min)];
            }
        }else{
            $time_max=strtotime($time_select.' '.$this->time_range_max);
            return ['data'=>date('Y-m-d H:i:s',$time_max)];
        }
    }

}