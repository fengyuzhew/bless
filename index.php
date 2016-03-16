<?php

define("TOKEN", "li570874734");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->receiveText($postObj);
                    break;
				    case "image":
                $resultStr = $this->receiveImage($postObj);
                break;
            case "location":
                $resultStr = $this->receiveLocation($postObj);
                break;
            case "voice":
                $resultStr = $this->receiveVoice($postObj);
                break;
            case "video":
                $resultStr = $this->receiveVideo($postObj);
                break;
            case "link":
                $resultStr = $this->receiveLink($postObj);
                break;
            case "event":
                $resultStr = $this->receiveEvent($postObj);
                break;
            default:
                $resultStr = "unknow msg type: ".$RX_TYPE;
                break;
            }
            echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    private function receiveText($object)
    {
        $funcFlag = 0;
        $keyword = trim($object->Content);
        $resultStr = "";
        $contentStr = "";

        if($keyword == "文本"){
            $contentStr = "这是个文本消息";
            $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        }
        else if($keyword == "图文" || $keyword == "单图文"){
            $dateArray = array();
            $dateArray[] = array("Title"=>"单图文标题", 
                                "Description"=>"单图文内容", 
                                "Picurl"=>"http://hzkc.cn/images/index_11.jpg", 
                                "Url" =>"http://hzkc.cn/");
            $resultStr = $this->transmitNews($object, $dateArray, $funcFlag);
        }
        else if($keyword == "多图文"){
            $dateArray = array();
            $dateArray[] = array("Title"=>"多图文1标题", "Description"=>"", "Picurl"=>"http://hzkc.cn/images/index_11.jpg", "Url" =>"http://hzkc.cn/");
            $dateArray[] = array("Title"=>"多图文2标题", "Description"=>"", "Picurl"=>"http://hzkc.cn/images/index_11.jpg", "Url" =>"http://hzkc.cn/");
            $dateArray[] = array("Title"=>"多图文3标题", "Description"=>"", "Picurl"=>"http://hzkc.cn/images/index_11.jpg", "Url" =>"http://hzkc.cn/");
            $resultStr = $this->transmitNews($object, $dateArray, $funcFlag);
        }
        else if($keyword == "音乐"){
            $musicArray = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3","HQMusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3");
            $resultStr = $this->transmitMusic($object, $musicArray, $funcFlag);
        }
        return $resultStr;
    }
	private function receiveImage($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是图片，地址为：".$object->PicUrl;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }

    private function receiveLocation($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }

    private function receiveVoice($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是语音，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }

    private function receiveVideo($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是视频，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }

    private function receiveLink($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }



    private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr[] = array("Title" =>"欢迎关注开创网络", "Description" =>"开创网络提供移动互联网相关的产品及服务，包括传统网站、微信公众平台接口、手机版网站等", "Picurl" =>"http://hzkc.cn/wx/images/kcjj.jpg", "Url" =>"http://hzkc.cn/wx/");
				$resultStr = $this->transmitNews($object, $contentStr);
                break;  
				case "unsubscribe":
                $contentStr = "";
				$resultStr = $this->transmitText($object, $contentStr);
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
					
                    case "开创简介":
                    
					$contentStr[] = array("Title" =>"开创简介", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/uploadfiles/kcjj.jpg", "Url" =>"http://hzkc.cn/wx");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "最新资讯":
                    
					$contentStr[] = array("Title" =>"最新资讯", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/uploadfiles/djt.jpg", "Url" =>"http://hzkc.cn/wx/news.asp");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "案例欣赏":
                    
					$contentStr[] = array("Title" =>"案例欣赏", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154411894.jpg", "Url" =>"http://hzkc.cn/wx/tp.asp?newsort=2");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "联系方式":
                    
					$contentStr[] = array("Title" =>"联系方式", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/uploadfiles/lxfs.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=6");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "网站建设":
                    
					$contentStr[] = array("Title" =>"网站建设", "Description" =>"   随着网络日益普及，越来越来的企业通过互联网来寻求新的销售途径，对比传统媒体网络营销将是一项低投入，高收益的推广方式，因此，选择一个好的网页设计公司，选择一个真正专业且有多年经验的网页设计公司才能为您设计出好的网页，从而提高您公司的知名度、网站的访问量，让您的生意伙伴更容易的找到您，开创网络：您网页设计的首选。
", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/uploadfiles/wzjs.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=2");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "手机客户端":
                    
					$contentStr[] = array("Title" =>"手机客户端", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154345850.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=4");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "微信官网":
                    
					$contentStr[] = array("Title" =>"微信官网", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/uploadfiles/wxgw.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=3");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "移动应用APP":
                    
					$contentStr[] = array("Title" =>"移动应用APP", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154327202.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=5");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "搜狗竞价":
                    
					$contentStr[] = array("Title" =>"搜狗竞价", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154639825.jpg", "Url" =>"http://hzkc.cn/wx/jj.asp?id=7");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "刮刮卡":
                    
					$contentStr[] = array("Title" =>"刮刮卡", "Description" =>"    利用微信的强交互性，让您通过对互动流程、环节和方式的设计，运用各种设计活动从而实现与用户的互动交流,，包括优惠券推广、大转盘推广、刮刮卡抽奖等功能模块，商家通过发起营销活动，对已有客户进行再营销，通过不断更新补充主题，用户可以反复参与，并可带动周边朋友一起分享，从而形成极强的口碑营销效果。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154024345.jpg", "Url" =>"http://hzkc.cn/wx/ggk.asp");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "大转盘":
                    
					$contentStr[] = array("Title" =>"大转盘", "Description" =>"    利用微信的强交互性，让您通过对互动流程、环节和方式的设计，运用各种设计活动从而实现与用户的互动交流,，包括优惠券推广、大转盘推广、刮刮卡抽奖等功能模块，商家通过发起营销活动，对已有客户进行再营销，通过不断更新补充主题，用户可以反复参与，并可带动周边朋友一起分享，从而形成极强的口碑营销效果。
", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116153518537.jpg", "Url" =>"http://hzkc.cn/wx/dzp.asp");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "优惠券":
                    
					$contentStr[] = array("Title" =>"优惠券", "Description" =>"    利用微信的强交互性，让您通过对互动流程、环节和方式的设计，运用各种设计活动从而实现与用户的互动交流,，包括优惠券推广、大转盘推广、刮刮卡抽奖等功能模块，商家通过发起营销活动，对已有客户进行再营销，通过不断更新补充主题，用户可以反复参与，并可带动周边朋友一起分享，从而形成极强的口碑营销效果。
", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116153230158.jpg", "Url" =>"http://hzkc.cn/wx/yhq.asp");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    case "一键导航":
                    
					$contentStr[] = array("Title" =>"一键导航", "Description" =>"    开创网络成立于2000年元月，是杭州地区最早的专业提供互联网服务的网络公司，具有14年的互联网建站经验，包括WWW电脑网站、手机建站服务，企业微信官网，移动应用APP，微信公众平台服务号接口开发等互联网精准营销服务。", "Picurl" =>"http://hzkc.cn/wx/hzkchtadmin/UploadFiles/2013116154253738.jpg", "Url" =>"http://map.baidu.com/?shareurl=1&poiShareUid=07852b97df95abeec9e33cd4");
					$resultStr = $this->transmitNews($object, $contentStr);
					break;
                    
                    default:
                        $contentStr[] = array("Title" =>"开创网络", "Description" =>"您正在使用的开创网络的自定义菜单测试接口", "Picurl" =>"http://hzkc.cn/wx/images/kcjj.jpg", "Url" =>"http://hzkc.cn/wap");
						$resultStr = $this->transmitNews($object, $contentStr);
                        break;
                }
                break;
            default:
                $contentStr = "receive a new event: ".$object->Event;
				$resultStr = $this->transmitText($object, $contentStr);
                break; 
        }
        
        return $resultStr;
    }

    private function transmitText($object, $content, $flag = 0)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%d</FuncFlag>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function transmitNews($object, $arr_item, $flag = 0)
    {
        if(!is_array($arr_item))
            return;

        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['Picurl'], $item['Url']);

        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
<FuncFlag>%s</FuncFlag>
</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $flag);
        return $resultStr;
    }
    
    private function transmitMusic($object, $musicArray, $flag = 0)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
<FuncFlag>%d</FuncFlag>
</xml>";

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $flag);
        return $resultStr;
    }
	
}

?>