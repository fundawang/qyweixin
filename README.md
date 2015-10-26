# 微信企业号
Drupal 下的微信企业号模块

用法
====
1. 你必须先在微信企业号网站申请一个账户(包括测试账户)，并且获得相应的 Corp ID 和管理组 Secret。
   目前本模块只支持在一个site使用一组corpid和管理组secret。如果要使用具有不同权限的管理组 secret，必须部署多个site。

2. 在 Drupal 的扩展页面启用此模块。

插件
====
本模块不包含任何应用或Agent的实现，而使用Drupal的插件机制来管理Agent。意即，你必须在 **`\Drupal\qyweixn\AgentBase`** 这个类的基础上自行派生类，并将其放置在模块的 **`src/Plugin/QyWeixinAgent`** 目录下，作为微信企业号的插件。插件使用 Annotation 方式发现，所以foo模块的企业微信插件 (`foo/src/Plugin/QyWeixinAgent/Bar.php`) 应该类似这样：

<pre>

/**
 * @file
 * Contains \Drupal\foo\Plugin\QyWeixinAgent\Bar.
 */

namespace Drupal\foo\Plugin\QyWeixinAgent;

use Drupal\qyweixin\CorpBase;
use Drupal\qyweixin\AgentBase;

/**
 * foo interface for qyweixin.
 *
 * @QyWeixinAgent(
 *   id = "foo",
 * )
 */
class Bar extends AgentBase {
}
</pre>
本模块目前只支持插件类与应用(Agent)的一对一关系，无法将不同的应用对应到同一个类上。

插件可自行决定将配置表单放置在哪里。插件可以自定义路由或菜单，但若此配置仅仅与微信有关，可以写在`foo.links.task.yml`中，作为企业微信配置页的一个tab。如：
<pre>
foo.admin.qyweixin:
  title: 'Foo settings'
  route_name: foo.admin.qyweixin
  base_route: qyweixin.admin
</pre>

主动调用
--------
主动调用时，应该生成一个本插件的实例：

<pre>
use Drupal\qyweixin\MessageBase;
$agent=\Drupal::service('plugin.manager.qyweixin.agent')->createInstance('foo');
try {
	$msg=new MessageBase();
	$msg->setMsgType(MessageBase::MESSAGE_TYPE_TEXT)->setContent('Hello World')
	    ->setToUser(USER_ID);
	$agent->messageSend($msg);
} catch(\Exception $e) {
	var_dump($e->getMessage());
}
</pre>

请注意，本模块不会对错误信息进行任何处理，所有错误信息都将会以异常(Exception)的形式抛出，调用时务必要使用 `try...catch` 语句。

`\Drupal\qyweixin\CorpBase` 提供了一些静态方法可供调用，比如获得当前的 AccessToken、获得目前管理组secret可见的应用列表等等。
