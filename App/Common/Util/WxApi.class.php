<?php

namespace Common\Util;

class WxApi extends Base
{
    const ERROR_INVALID_CODE = 40029;

    private $gardenId;
    private $appId;
    private $appSecret;

    // 用户授权后的信息 包括openid 和 access_token
    private $userGrant;

    public function __construct($gardenId = 0)
    {
        $this->gardenId = $gardenId;
        $config = D('WxConfig')->getWxConfigInfo($this->gardenId);
        if (!$this->isConfigActive($config)) {
            $this->error('园区暂未配置公众号信息,无法提供服务');
        }
        $this->appId = $config['appid'];
        $this->appSecret = $config['appsecret'];
    }

    /**
     * 判断配置是否有效
     * @param $config
     * @return boolean
     * User: hjun
     * Date: 2018-05-17 09:17:07
     * Update: 2018-05-17 09:17:07
     * Version: 1.00
     */
    public function isConfigActive($config)
    {
        return !empty($config['appid']) && !empty($config['appsecret']);
    }

    /**
     * 是否是无效的code
     * @param $result
     * @return boolean
     * User: hjun
     * Date: 2018-05-17 09:48:47
     * Update: 2018-05-17 09:48:47
     * Version: 1.00
     */
    public function isInvalidCode($result)
    {
        return $result['code'] === self::ERROR_INVALID_CODE;
    }

    /**
     * 获取用户授权信息
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-17 10:02:41
     * Update: 2018-05-17 10:02:41
     * Version: 1.00
     */
    public function getUserGrantInfo()
    {
        function get($appid, $appsecret)
        {
            if (!isset($_GET['code'])) {
                $thisurl = urlencode(getCurPageURL());
                $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid .
                    '&redirect_uri=' . $thisurl . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect&connect_redirect=1';
                jump($url, 1);
            } else {
                $code = $_GET['code'];
            }
            $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid .
                '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
            $tokenData = httpRequest($get_token_url);
            $tokenData = json_decode($tokenData, 1);
            if (empty($tokenData['openid'])) {
                return getReturn($tokenData['errcode'], $tokenData['errmsg']);
            }
            return getReturn(CODE_SUCCESS, 'success', $tokenData);
        }

        $appid = $this->appId;
        $appsecret = $this->appSecret;
        // 尝试3次获取
        for ($i = 0; $i < 3; $i++) {
            $result = get($appid, $appsecret);
            if (isSuccess($result)) {
                $this->userGrant = $result['data'];
                return $result['data'];
            } elseif ($this->isInvalidCode($result)) {
                unset($_GET['code']);
            }
        }
        $this->error($result['msg']);
    }

    /**
     * 获取openid
     * @return string
     * User: hjun
     * Date: 2018-05-17 09:25:49
     * Update: 2018-05-17 09:25:49
     * Version: 1.00
     */
    public function getOpenId()
    {
        if (isset($this->userGrant)) {
            return $this->userGrant['openid'];
        }
        $this->getUserGrantInfo();
        return $this->userGrant['openid'];
    }

    /**
     * 获取网页授权的access_token
     * @return string
     * User: hjun
     * Date: 2018-05-17 10:05:48
     * Update: 2018-05-17 10:05:48
     * Version: 1.00
     */
    public function getGrantAccessToken()
    {
        if (isset($this->userGrant['access_token'])) {
            return $this->userGrant['access_token'];
        }
        $this->getUserGrantInfo();
        return $this->userGrant['openid'];
    }

    /**
     * 获取用户基本信息
     * @return array
     * User: hjun
     * Date: 2018-05-17 10:00:59
     * Update: 2018-05-17 10:00:59
     * Version: 1.00
     */
    public function getUserBaseInfo()
    {
        $openId = $this->getOpenId();
        $accessToken = $this->getGrantAccessToken();
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $accessToken . "&openid=" . $openId . "&lang=zh_CN";
        $usermsg = json_decode(httpRequest($url), true);
        if (!empty($usermsg['errcode'])) {
            $this->error($usermsg['errmsg']);
        }
        return $usermsg;
    }
}