<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use Exception;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class City extends Backend
{

  /**
   * City模型对象
   * @var \app\admin\model\City
   */
  protected $model = null;

  public function _initialize()
  {
    parent::_initialize();
    $this->model = new \app\admin\model\City;

  }

  public function import()
  {
    parent::import();
  }

  /**
   * 查看
   */
  public function index()
  {
    //设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if ($this->request->isAjax()) {
      //如果发送的来源是Selectpage，则转发到Selectpage
      if ($this->request->request('keyField')) {
        return $this->selectpage();
      }
      list($where, $sort, $order, $offset, $limit) = $this->buildparams();

      $list = $this->model
        ->where($where)
        ->order($sort, $order)
        ->paginate($limit);

      print_r($list);
      exit;
      $result = array("total" => $list->total(), "rows" => $list->items());

      return json($result);
    }
    return $this->view->fetch();
  }

  /**
   * 添加
   */
  public function add()
  {
    if ($this->request->isPost()) {
      $params = $this->request->post("row/a");
      if ($params) {
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
          $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
          //是否采用模型验证
          if ($this->modelValidate) {
            $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
            $this->model->validateFailException(true)->validate($validate);
          }
          $name = explode('/', $params['name']);
          $params['province'] = $name[0];
          $params['city'] = $name[1];
          $params['district'] = $name[2];
          $result = $this->model->allowField(true)->save($params);
          Db::commit();
        } catch (ValidateException $e) {
          Db::rollback();
          $this->error($e->getMessage());
        } catch (PDOException $e) {
          Db::rollback();
          $this->error($e->getMessage());
        } catch (Exception $e) {
          Db::rollback();
          $this->error($e->getMessage());
        }
        if ($result !== false) {
          $this->success();
        } else {
          $this->error(__('No rows were inserted'));
        }
      }
      $this->error(__('Parameter %s can not be empty', ''));
    }
    return $this->view->fetch();
  }

  /**
   * 编辑
   */
  public function edit($ids = null)
  {
    $row = $this->model->get($ids);
    if (!$row) {
      $this->error(__('No Results were found'));
    }
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      if (!in_array($row[$this->dataLimitField], $adminIds)) {
        $this->error(__('You have no permission'));
      }
    }
    if ($this->request->isPost()) {
      $params = $this->request->post("row/a");
      if ($params) {
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
          //是否采用模型验证
          if ($this->modelValidate) {
            $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
            $row->validateFailException(true)->validate($validate);
          }
          $name = explode('/', $params['name']);
          $params['province'] = $name[0];
          $params['city'] = $name[1];
          $params['district'] = $name[2];
          $result = $row->allowField(true)->save($params);
          Db::commit();
        } catch (ValidateException $e) {
          Db::rollback();
          $this->error($e->getMessage());
        } catch (PDOException $e) {
          Db::rollback();
          $this->error($e->getMessage());
        } catch (Exception $e) {
          Db::rollback();
          $this->error($e->getMessage());
        }
        if ($result !== false) {
          $this->success();
        } else {
          $this->error(__('No rows were updated'));
        }
      }
      $this->error(__('Parameter %s can not be empty', ''));
    }
    $row['name'] = $row['province'] . '/' . $row['city'] . '/' . $row['district'];
    $this->view->assign("row", $row);
    return $this->view->fetch();
  }
  /**
   * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
   * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
   * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
   */


  /**
   * Selectpage的实现方法
   *
   * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
   * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
   *
   */
  protected function selectpage()
  {
    //设置过滤方法
    $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

    //搜索关键词,客户端输入以空格分开,这里接收为数组
    $word = (array)$this->request->request("q_word/a");
    //当前页
    $page = $this->request->request("pageNumber");
    //分页大小
    $pagesize = $this->request->request("pageSize");
    //搜索条件
    $andor = $this->request->request("andOr", "and", "strtoupper");
    //排序方式
    $orderby = (array)$this->request->request("orderBy/a");
    //显示的字段
    $field = $this->request->request("showField");
    //主键
    $primarykey = $this->request->request("keyField");
    //主键值
    $primaryvalue = $this->request->request("keyValue");
    //搜索字段
    $searchfield = (array)$this->request->request("searchField/a");
    //自定义搜索条件
    $custom = (array)$this->request->request("custom/a");
    //是否返回树形结构
    $istree = $this->request->request("isTree", 0);
    $ishtml = $this->request->request("isHtml", 0);
    if ($istree) {
      $word = [];
      $pagesize = 999999;
    }
    $order = [];
    foreach ($orderby as $k => $v) {
      $order[$v[0]] = $v[1];
    }
    $field = $field ? $field : 'name';

    //如果有primaryvalue,说明当前是初始化传值
    if ($primaryvalue !== null) {
      $where = [$primarykey => ['in', $primaryvalue]];
      $pagesize = 999999;
    } else {
      $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
        $logic = $andor == 'AND' ? '&' : '|';
        $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
        $searchfield = str_replace(',', $logic, $searchfield);
        $word = array_filter(array_unique($word));
        if (count($word) == 1) {
          $query->where($searchfield, "like", "%" . reset($word) . "%");
        } else {
          $query->where(function ($query) use ($word, $searchfield) {
            foreach ($word as $index => $item) {
              $query->whereOr(function ($query) use ($item, $searchfield) {
                $query->where($searchfield, "like", "%{$item}%");
              });
            }
          });
        }
        if ($custom && is_array($custom)) {
          foreach ($custom as $k => $v) {
            if (is_array($v) && 2 == count($v)) {
              $query->where($k, trim($v[0]), $v[1]);
            } else {
              $query->where($k, '=', $v);
            }
          }
        }
      };
    }
    $adminIds = $this->getDataLimitAdminIds();
    if (is_array($adminIds)) {
      $this->model->where($this->dataLimitField, 'in', $adminIds);
    }
    $list = [];
    $total = $this->model->where($where)->count();
    if ($total > 0) {
      if (is_array($adminIds)) {
        $this->model->where($this->dataLimitField, 'in', $adminIds);
      }

      $fields = is_array($this->selectpageFields) ? $this->selectpageFields : ($this->selectpageFields && $this->selectpageFields != '*' ? explode(',', $this->selectpageFields) : []);

      //如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
      if ($primaryvalue !== null && preg_match("/^[a-z0-9_\-]+$/i", $primarykey)) {
        $primaryvalue = array_unique(is_array($primaryvalue) ? $primaryvalue : explode(',', $primaryvalue));
        //修复自定义data-primary-key为字符串内容时，给排序字段添加上引号
        $primaryvalue = array_map(function ($value) {
          return '\'' . $value . '\'';
        }, $primaryvalue);

        $primaryvalue = implode(',', $primaryvalue);

        $this->model->orderRaw("FIELD(`{$primarykey}`, {$primaryvalue})");
      } else {
        $this->model->order($order);
      }

      $datalist = $this->model->where($where)
        ->page($page, $pagesize)
        ->select();

      foreach ($datalist as $index => $item) {
        unset($item['password'], $item['salt']);
        if ($this->selectpageFields == '*') {
          $result = [
            $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
            $field => isset($item[$field]) ? $item[$field] : '',
          ];
        } else {
          $result = array_intersect_key(($item instanceof Model ? $item->toArray() : (array)$item), array_flip($fields));
        }
        $result['pid'] = isset($item['pid']) ? $item['pid'] : (isset($item['parent_id']) ? $item['parent_id'] : 0);
        $list[] = $result;
      }
      if ($istree && !$primaryvalue) {
        $tree = Tree::instance();
        $tree->init(collection($list)->toArray(), 'pid');
        $list = $tree->getTreeList($tree->getTreeArray(0), $field);
        if (!$ishtml) {
          foreach ($list as &$item) {
            $item = str_replace('&nbsp;', ' ', $item);
          }
          unset($item);
        }
      }
    }
    //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
    return json(['list' => $list, 'total' => $total]);
  }
}
