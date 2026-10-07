<?php

namespace App\Http\Controllers\Backend\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\V1\User\UserCatalogueService;
use App\Repositories\User\UserCatalogueRepository;
use App\Repositories\User\PermissionRepository;


use App\Http\Requests\User\StoreUserCatalogueRequest;

class UserCatalogueController extends Controller
{
    protected $userCatalogueService;
    protected $userCatalogueRepository;
    protected $permissionRepository;

    public function __construct(
        UserCatalogueService $userCatalogueService,
        UserCatalogueRepository $userCatalogueRepository,
        PermissionRepository $permissionRepository
    ){
        $this->userCatalogueService = $userCatalogueService;
        $this->userCatalogueRepository = $userCatalogueRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function index(Request $request){
        $this->authorize('modules', 'user.catalogue.index');
        $userCatalogues = $this->userCatalogueService->paginate($request);
        $config = [
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
            ],
            'css' => [
                'backend/css/plugins/switchery/switchery.css',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ],
            'model' => 'UserCatalogue',
        ];
        $config['seo'] = config('apps.usercatalogue');
        $template = 'backend.user.catalogue.index';
        return view('backend.dashboard.layout', compact(
            'template',
            'config',
            'userCatalogues'
        ));
    }

    public function create(){
        $this->authorize('modules', 'user.catalogue.create');
        $config['seo'] = config('apps.usercatalogue');
        $config['method'] = 'create';
        $template = 'backend.user.catalogue.store';
        return view('backend.dashboard.layout', compact(
            'template',
            'config',
        ));
    }

    public function store(StoreUserCatalogueRequest $request){
        if($this->userCatalogueService->create($request)){
            return redirect()->route('user.catalogue.index')->with('success','Thêm mới bản ghi thành công');
        }
        return redirect()->route('user.catalogue.index')->with('error','Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id){
        $this->authorize('modules', 'user.catalogue.update');
        $userCatalogue = $this->userCatalogueRepository->findById($id);
        $config['seo'] = config('apps.usercatalogue');
        $config['method'] = 'edit';
        $template = 'backend.user.catalogue.store';
        return view('backend.dashboard.layout', compact(
            'template',
            'config',
            'userCatalogue',
        ));
    }

    public function update($id, StoreUserCatalogueRequest $request){
        if($this->userCatalogueService->update($id, $request)){
            return redirect()->route('user.catalogue.index')->with('success','Cập nhật bản ghi thành công');
        }
        return redirect()->route('user.catalogue.index')->with('error','Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id){
        $this->authorize('modules', 'user.catalogue.destroy');
        $config['seo'] = config('apps.usercatalogue');
        $userCatalogue = $this->userCatalogueRepository->findById($id);
        $template = 'backend.user.catalogue.delete';
        return view('backend.dashboard.layout', compact(
            'template',
            'userCatalogue',
            'config',
        ));
    }

    public function destroy($id){
        if($this->userCatalogueService->destroy($id)){
            return redirect()->route('user.catalogue.index')->with('success','Xóa bản ghi thành công');
        }
        return redirect()->route('user.catalogue.index')->with('error','Xóa bản ghi không thành công. Hãy thử lại');
    }

    public function permission(){
        $this->authorize('modules', 'user.catalogue.permission');
        $userCatalogues = $this->userCatalogueRepository->all(['permissions']);

        $permissions = $this->permissionRepository->all();
        $config['seo'] = __('messages.userCatalogue');
        $template = 'backend.user.catalogue.permission';
        return view('backend.dashboard.layout', compact(
            'template',
            'userCatalogues',
            'permissions',
            'config',
        ));
    }

    public function updatePermission(Request $request){
        if($this->userCatalogueService->setPermission($request)){
            return redirect()->route('user.catalogue.index')->with('success','Cập nhật quyền thành công');
        }
        return redirect()->route('user.catalogue.index')->with('error','Có vấn đề xảy ra, Hãy thử lại');
    }

    /**
     * Bật/tắt cờ "Là nhóm nhân viên kinh doanh" ngay trong danh sách nhóm.
     *
     * Cờ này quyết định khá nhiều thứ: thành viên của nhóm đăng nhập ở /sale thay
     * vì trang quản trị, và họ có xuất hiện ở ô chọn "nhân viên phụ trách" trong
     * form dự án hay không. Trước đây muốn đổi phải mở từng nhóm ra sửa, mà danh
     * sách thì không hiện cờ - nên không ai biết nhóm nào đang bật.
     */
    public function doiCoSale(Request $request, $id)
    {
        $this->authorize('modules', 'user.catalogue.update');

        $nhom = $this->userCatalogueRepository->findById($id);

        if (!$nhom) {
            return response()->json(['ok' => false, 'loi' => 'Không tìm thấy nhóm thành viên.'], 404);
        }

        $bat = $request->boolean('is_sale');
        $this->userCatalogueRepository->update($id, ['is_sale' => $bat ? 1 : 0]);

        return response()->json([
            'ok' => true,
            'is_sale' => $bat ? 1 : 0,
            'so_thanh_vien' => $nhom->users()->count(),
        ]);
    }

}
