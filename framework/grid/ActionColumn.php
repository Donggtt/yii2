<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ActionColumn是[[GridView]]一列小部件,显示按钮用于查看和操作的项目。
 *
 * 去增加动作列 配置如下:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => ActionColumn::className(),
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * 有关ActionColumn的更多详细信息和用法信息，请参阅《[指南》中关于数据小部件的文章
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public $headerOptions = ['class' => 'action-column'];
    /**
     * @var string 控制器处理ID指定的动作
     * 如果没有设置, 它将使用当前活动控制器, 这个属性主要使用的
     * [[urlCreator]]创建url不同的动作。这个属性的值将前缀
     * 每个动作名称形成的路由动作。
     */
    public $controller;
    /**
     * @var string 模板用于写操作列中的每个单元格。
     *令牌在花括号被当作控制器动作的id(也称为*按钮名称*的动作列)。
     * 他们将被相应的按钮渲染[[button]]中指定的回调。
     * 例如,令牌“{view}”将取代了回调的结果”按钮(“view”)”。
     * 如果不能找到一个回调,令牌将被替换为一个空字符串。
     *
     * 例如，要有视图和更新按钮，可以将操作列添加到GridView列，如下所示:
     *
     * ```php
     * ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update}'],
     * ```
     *
     * @see buttons
     */
    public $template = '{view} {update} {delete}';
    /**
     * @var array button渲染回调,数组键button名称(没有花括号),
     * 和值对应的按钮渲染回调。应该使用以下回调
     * 签名:
     *
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * $url在哪个url列创建按钮,$model是一个对象  
     * 被渲染为当前行,和$key是模型的关键数据提供程序的数组
     *
     * 您可以添加进一步条件按钮,,例如只显示它
     * 当模型是可编辑(这里假设您有一个状态字段说明):
     *
     * ```php
     * [
     *     'update' => function ($url, $model, $key) {
     *         return $model->status === 'editable' ? Html::a('Update', $url) : '';
     *     },
     * ],
     * ```
     */
    public $buttons = [];
    /** @var 每个按钮的数组可见性条件。数组键是按钮名称
     * 值是布尔真/假或匿名函数。未在中指定按钮名称时
     * 默认情况下会显示这个数组
     * 回调必须使用以下签名
     *
     * ```php
     * function ($model, $key, $index) {
     *     return $model->status === 'editable';
     * }
     * ```
     *
     * 或者你可以传递这个布尔值
     *
     * ```php
     * [
     *     'update' => \Yii::$app->user->can('update'),
     * ],
     * ```
     * @since 2.0.7
     */
    public $visibleButtons = [];
    /**
     * @var callable 一个回调URL使用指定的模型信息创建一个按钮。
     * 回调的签名应该一样[[createUrl()]]。
     * 自2 . 0 . 10以来，它可以接受其他参数，这些参数指的是列实例本身:
     *
     * ```php
     * function (string $action, mixed $model, mixed $key, integer $index, ActionColumn $this) {
     *     //return string;
     * }
     * ```
     *
     * 如果未设置此属性，将使用[ [ CreateURL ( ) ] ]创建按钮URL。
     * 
     */
    public $urlCreator;
    /**
     * @var array html选项适用于[[initDefaultButtons()|默认按钮]]。
     * @since 2.0.4
     */
    public $buttonOptions = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->initDefaultButtons();
    }

    /**
     * 初始化呈现回调的默认按钮。
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
        ]);
    }

    /**
     * 初始化单个按钮的默认按钮呈现回调。
     * @param string $name 按钮名称因为它被写在模板中
     * @param string $iconName Bootstrap的glyphicon字体图标使它变得唯一
     * @param array $additionalOptions附加选项数组
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

    /**
     * 创建一个URL模式为model中的action
     * 调用此方法对于每一个按钮,每一行。
     * @param string $action 按钮名称 (或操作ID)
     * @param \yii\db\ActiveRecordInterface $模型 数据模型
     *  与数据模型相关联的关键
     * @param int $index 当前行索引
     * @返回string创建URL
     */
    public function createUrl($action, $model, $key, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        }

        $params = is_array($key) ? $key : ['id' => (string) $key];
        $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

        return Url::toRoute($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible && isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $model, $key, $index);
                return call_user_func($this->buttons[$name], $url, $model, $key);
            }

            return '';
        }, $this->template);
    }
}
