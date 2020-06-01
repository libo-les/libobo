<?php

namespace App\Http\Controllers\Admin;

use App\Http\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends BaseController {
	/**
	 * [index 模块列表]
	 * @return [type] [description]
	 */
	public function index(Request $request) {

		$res = Module::get()->toArray();

		$items = array();
		foreach ($res as $value) {
			$items[$value['id']] = $value;
		}
		//第二部 遍历数据 生成树状结构
		$tree = array();
		foreach ($items as $key => $value) {
			//如果pid这个节点存在
			if (isset($items[$value['pid']])) {
				//把当前的$value放到pid节点的son中
				$items[$value['pid']]['children'][] = &$items[$key];
			} else {
				$tree[] = &$items[$key];
			}
		}
		//排序
		foreach ($tree as $key => $value) {
			if (in_array('children', $value) && !empty($value['children'])) {
				array_multisort(array_column($tree[$key]['children'], 'sort'), SORT_ASC, SORT_NUMERIC, $tree[$key]['children']);
				foreach ($value['children'] as $k => $v) {
					if (in_array('children', $v) && !empty($v['children'])) {
						foreach ($v['children'] as $i => $j) {
							array_multisort(array_column($tree[$key]['children'][$k]['children'], 'sort'), SORT_ASC, SORT_NUMERIC, $tree[$key]['children'][$k]['children']);
						}
					}
				}
			}

		}

		return apiReturn(0, '权限列表', $tree);
	}
	/**
	 * [addAuthority 添加模块]
	 * @param Request $request [description]
	 */
	public function addAuthority(Request $request) {
		$data = $request->all();
		$level = module::where('id', $data['pid'])->value('level');
		if ($level < 3) {
			$data['level'] = $level + 1;
		} else {
			$data['level'] = 3;
		}
		$data['created_at'] = time();
		$data['updated_at'] = time();
		$res = Module::insert($data);
		if ($res == 1) {
			return apiReturn(0, '添加成功');
		} else {
			return apiReturn(1, '添加失败');
		}
	}
	/**
	 * [authorityDetail 模块信息]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function authorityDetail(Request $request) {

		$id = $request->input('id');
		$res = Module::where('id', $id)->first();
		return apiReturn(0, '权限信息', $res);
	}
	/**
	 * [updauthority 修改模块]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function updauthority(Request $request) {
		$data = $request->all();
		$res = Module::where('id', $data['id'])->update($data);
		if ($res == 1) {
			return apiReturn(0, '修改成功');
		} else {
			return apiReturn(1, '修改失败');
		}
	}
	/**
	 * [delauthority 删除模块]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function delauthority(Request $request) {
		$id = $request->input('id');
		$son = Module::where('pid', $id)->get()->toArray();
		if (count($son) != 0) {
			return apiReturn(1, '还有子分类，不能删除');
		} else {
			$res = Module::where('id', $id)->delete();
			if ($res == 1) {
				return apiReturn(0, '删除成功');
			} else {
				return apiReturn(1, '删除失败');
			}
		}

	}
	/**
	 * [authorityGroup 上级菜单列表]
	 * @return [type] [description]
	 */
	public function authorityGroup(Request $request) {
		$level = $request->input('level');

		switch ($level) {
		case '1':
			return apiReturn(0, '上级菜单列表');
			break;
		case '2':
			$res = Module::select('id', 'module_name', 'pid')->where('is_menu', 1)->whereIn('level', [1])->get()->toArray();
			break;
		case '3':
			$res = Module::select('id', 'module_name', 'pid')->where('is_menu', 1)->whereIn('level', [1, 2])->get()->toArray();
			break;
		default:
			$res = Module::select('id', 'module_name', 'pid')->where('is_menu', 1)->whereIn('level', [1, 2, 3])->get()->toArray();
			break;
		}
		$items = array();
		foreach ($res as $value) {
			$items[$value['id']] = $value;
		}
		//第二部 遍历数据 生成树状结构
		$tree = array();
		foreach ($items as $key => $value) {
			//如果pid这个节点存在
			if (isset($items[$value['pid']])) {
				//把当前的$value放到pid节点的son中 注意 这里传递的是引用 为什么呢？
				$items[$value['pid']]['children'][] = &$items[$key];
			} else {
				$tree[] = &$items[$key];
			}
		}
		return apiReturn(0, '上级菜单列表', $tree);
	}

}
