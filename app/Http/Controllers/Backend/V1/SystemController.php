<?php

namespace App\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\System;

use App\Services\V1\Core\SystemService;
use App\Repositories\Core\SystemRepository;

use App\Models\Language;

class SystemController extends Controller
{
    protected $systemLibrary;
    protected $systemService;
    protected $systemRepository;
    protected $language;

    public function __construct(
        System $systemLibrary,
        SystemService $systemService,
        SystemRepository $systemRepository,

    ){
        $this->middleware(function($request, $next){
            $locale = app()->getLocale(); // vn en cn
            $language = Language::where('canonical', $locale)->first();
            $this->language = $language->id;
            return $next($request);
        });
        $this->systemLibrary = $systemLibrary;
        $this->systemService = $systemService;
        $this->systemRepository = $systemRepository;
    }

    public function index(){
        
        $systemConfig = $this->systemLibrary->config();
        $systems = convert_array($this->systemRepository->findByCondition(
            [
                ['language_id', '=', $this->language]
            ], TRUE
        ), 'keyword', 'content');
        
        $config = $this->config();
        $config['seo'] = __('messages.system');
        $template = 'backend.system.index';
        return view('backend.dashboard.layout', compact(
            'template',
            'config',
            'systemConfig',
            'systems',
        ));
    }

    public function store(Request $request){
        if($this->systemService->save($request, $this->language)){
            return redirect()->route('system.index')->with('success','Cập nhật bản ghi thành công');
        }
        return redirect()->route('system.index')->with('error','Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function translate($languageId = 0){

        $systemConfig = $this->systemLibrary->config();
        $systems = convert_array($this->systemRepository->findByCondition(
            [
                ['language_id', '=', $languageId]
            ], TRUE
        ), 'keyword', 'content');
        $config = $this->config();
        $config['seo'] = __('messages.system');
        $config['method'] = 'translate';
        $template = 'backend.system.index';
        return view('backend.dashboard.layout', compact(
            'template',
            'config',
            'systemConfig',
            'languageId',
            'systems',
        ));
    }

    public function saveTranslate(Request $request, $languageId){
        if($this->systemService->save($request, $languageId)){
            return redirect()->route('system.translate', ['languageId' => $languageId])->with('success','Cập nhật bản ghi thành công');
        }
        return redirect()->route('system.index')->with('error','Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    /**
     * Kiểm tra cấu hình Telegram ngay trong trang quản trị.
     *
     * Vi sao can: cau hinh Telegram sai thi bieu hien duy nhat la "khong thay
     * thong bao nao ve may", ma co it nhat bon nguyen nhan khac nhau. Ngoi doan
     * tung cai rat mat thoi gian, nen cho quan tri bam mot nut ra cau tra loi.
     *
     * @param  string  $viec  token | chat-id | gui-thu
     */
    public function kiemTraTelegram(Request $request, \App\Services\Noxh\TelegramService $telegram)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('system.index');
        }

        $viec = (string) $request->input('viec', 'token');

        $ketQua = match ($viec) {
            'chat-id' => $telegram->timChatId(),
            'gui-thu' => $telegram->guiThu(),
            default => $telegram->kiemTraToken(),
        };

        return response()->json($ketQua);
    }
    
    private function config(){
        return [
           'extendJs' => true
        ];
    }

}

