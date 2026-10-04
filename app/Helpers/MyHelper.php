<?php

use App\Enums\PromotionEnum;

if(!function_exists('convertRevenueChartData')){
    function convertRevenueChartData($chartData, $data = 'monthly_revenue', $label = 'month' , $text = 'Tháng'){
        $newArray = [];
        if(!is_null($chartData) && count($chartData)){
            foreach($chartData as $key => $val){
                $newArray['data'][] = $val->{$data};
                $newArray['label'][] = $text.' '.$val->{$label};
            }
        }
        return $newArray;
    }
}


if(!function_exists('growHtml')){
    function growHtml($grow){
        if($grow > 0){
            return '<div class="stat-percent font-bold text-success">'.$grow.'% <i class="fa fa-level-up"></i></div>';
        }else{
            return '<div class="stat-percent font-bold text-danger">'.$grow.'% <i class="fa fa-level-down"></i></div>';
        }
    }
}

if(!function_exists('growth')){
    function growth($currentValue, $previousValue){
        $divison = ($previousValue == 0) ? 1 : $previousValue;
        $grow =  ($currentValue - $previousValue) / $divison * 100;
        return number_format($grow, 1);
    }
}

if(!function_exists('pre')){
    function pre($data = ''){
        echo '<pre>';
        print_r($data);
        echo '<pre>';
        die();
    }
}

if(!function_exists('image')){
    function image($image){
        
        if(is_null($image)) return 'backend/img/not-found.jpg';

        $image = str_replace('/public/', '/', $image);

        return $image;
    }
}

if(!function_exists('getGiaoHangNhanhToken')){
    function getGiaoHangNhanhToken(){
       return '21e62550-a35f-11ef-a89d-dab02cbaab48';
    }
}


if(!function_exists('convert_price')){
    function convert_price(mixed $price = '', $flag = false){
        if($price === null) return 0;
        return ($flag === false) ? str_replace('.','', $price) : number_format($price, 0, ',', '.');
    }
}

if(!function_exists('getPercent')){
    function getPercent($product = null, $discountValue = 0){
        return ($product->price > 0) ? round($discountValue/$product->price*100) : 0;
    
    }
}

if(!function_exists('getPromotionPrice')){
    function getPromotionPrice($priceMain = 0, $discountValue = 0){
       

        return $priceMain - $discountValue;
    
    }
}


// if(!function_exists('getPrice')){
//     function getPrice($product = null){
//         $result = [
//             'price' => $product->price, 
//             'priceSale' => 0,
//             'percent' => 0, 
//             'html' => ''
//         ];

//         if($product->price == 0){

//             $result['html'] .= '<div class="price mt10">';
//                 $result['html'] .= '<div class="price-sale">Liên Hệ</div>';
//             $result['html'] .= '</div>';
//             return $result;
//         }

//         if(isset($product->promotions) && isset($product->promotions->discountType)){
//             $result['percent'] = getPercent($product, $product->promotions->discount);
//             if($product->promotions->discountValue > 0){
//                 $result['priceSale'] = getPromotionPrice($product->price, $product->promotions->discount);
//             }
//         }
//         $result['html'] .= '<div class="price uk-flex uk-flex-middle mt10">';
//             $result['html'] .= '<div class="price-sale">'.(($result['priceSale'] > 0) ? convert_price($result['priceSale'], true) : convert_price($result['price'], true) ).'<span class="currency">₫</span></div>';
//             if($result['priceSale'] > 0){
//                 $result['html'] .= '<div class="price-old uk-flex uk-flex-middle">'.convert_price($result['price'], true).'đ <div class="percent"><div class="percent-value">-'.$result['percent'].'%</div></div></div>';
                
//             }
//         $result['html'] .= '</div>';
//         return $result;
//     }
// }

if(!function_exists('getPrice')){
    function getPrice($product = null){
        $result = [
            'price' => 0, 
            'priceSale' => 0,
            'percent' => 0, 
            'html' => '<div class="price mt10"><div class="price-sale">Liên Hệ</div></div>'
        ];
        return $result;
    }
}

if(!function_exists('getVariantPrice')){
    function getVariantPrice($variant, $variantPromotion){
        $result = [
            'price' => 0, 
            'priceSale' => 0,
            'percent' => 0, 
            'html' => '<div class="price mt10"><div class="price-sale">Liên Hệ</div></div>'
        ];
        return $result;
    }
}


if(!function_exists('getReview')){
    function getReview($product = null){

        /** @var $product Product */
        $totalReviews = $product->reviews()->count();
        $totalRate = number_format($product->reviews()->avg('score'), 1);
        $starPercent = ($totalReviews == 0) ? '0' : $totalRate/5*100;

        return [
            'star' => $starPercent,
            'count' => $totalReviews,
            'totalRate' => $totalRate
        ];
        
    }
}


if(!function_exists('convert_array')){
    function convert_array($system = null, $keyword = '', $value = ''){
        $temp = [];
        if(is_array($system)){
            foreach($system as $key => $val){
                $temp[$val[$keyword]] = $val[$value];
            }
        }
        if(is_object($system)){
            foreach($system as $key => $val){
                $temp[$val->{$keyword}] = $val->{$value};
            }
        }

        return $temp;
    }
}

if(!function_exists('convertDateTime')){
    function convertDateTime(string $date = '', string $format = 'd/m/Y H:i', string $inputDateFormat = 'Y-m-d H:i:s'){
       $carbonDate = \Carbon\Carbon::createFromFormat($inputDateFormat, $date);

       return $carbonDate->format($format);
    }
}

if(!function_exists('renderDiscountInformation')){
    function renderDiscountInformation($promotion = null){
        if($promotion->method === 'product_and_quantity'){
            $discountValue = $promotion->discountInformation['info']['discountValue'];
            $discountType = ($promotion->discountInformation['info']['discountType'] == 'percent') ? '%' : 'đ';
            return '<span class="label label-success">'.$discountValue.$discountType.' </span>';
        }
        return  '<div><a href="'.route('promotion.edit', $promotion->id).'">Xem chi tiết</a></div>';
    }
}

if(!function_exists('renderDiscountVoucher')){
    function renderDiscountVoucher($voucher = null){
        $discount_value = $voucher->discount_value;
        $discount_type = ($voucher->discount_type == 'PERCENTAGE') ? '%' : 'đ';
        return '<span class="label label-success">'.$discount_value.$discount_type.' </span>';
    }
}

if(!function_exists('renderSystemInput')){
    function renderSystemInput(string $name = '', $systems = null){
        return '<input 
            type="text"
            name="config['.$name.']"
            value="'.old($name, ($systems[$name]) ?? '').'"
            class="form-control"
            placeholder=""
            autocomplete="off"
        >';
    }
}


if(!function_exists('renderSystemImages')){
    /**
     * O nhap duong dan anh, kem ANH XEM TRUOC ben phai.
     *
     * Truoc day cho nay chi co mot <input>: quan tri go/re mot duong dan ma khong
     * thay minh vua chon anh nao. Anh xem truoc lay dung gia tri dang luu, cap
     * nhat khi go tay (su kien input/change) va khi chon xong trong CKFinder
     * (xem HT.capNhatXemTruocAnh trong public/vendor/backend/library/finder.js).
     */
    function renderSystemImages(string $name = '', $systems = null){
        // CSS chi in ra MOT lan cho ca trang, du trang co bao nhieu o anh.
        static $daInCss = false;
        $css = '';
        if(!$daInCss){
            $daInCss = true;
            $css = '<style>
                .o-anh{display:flex;align-items:flex-start;gap:10px}
                .o-anh>.form-control{flex:1 1 auto;min-width:0}
                .o-anh__xem{flex:0 0 auto;width:118px;height:66px;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;overflow:hidden}
                .o-anh__xem img{display:block;width:100%;height:100%;object-fit:cover}
                .o-anh__xem.trong{display:none}
            </style>';
        }

        $giaTri = (string) old($name, ($systems[$name]) ?? '');

        return $css.'<div class="o-anh">
            <input
                type="text"
                name="config['.$name.']"
                value="'.e($giaTri).'"
                class="form-control upload-image"
                placeholder=""
                autocomplete="off"
            >
            <span class="o-anh__xem'.($giaTri === '' ? ' trong' : '').'"><img src="'.e($giaTri).'" alt=""></span>
        </div>';
    }
}


if(!function_exists('renderSystemTextarea')){
    function renderSystemTextarea(string $name = '', $systems = null){
        return '<textarea name="config['.$name.']" class="form-control system-textarea">'.old($name, ($systems[$name]) ?? '').'</textarea>';
    }
}

if(!function_exists('renderSystemEditor')){
    function renderSystemEditor(string $name = '', $systems = null){
        return '<textarea name="config['.$name.']" id="'.$name.'" class="form-control system-textarea ck-editor">'.old($name, ($systems[$name]) ?? '').'</textarea>';
    }
}

if(!function_exists('renderSystemLink')){
    function renderSystemLink(array $item = [], $systems = null){
        return (isset($item['link'])) ? '<a class="system-link" target="'.$item['link']['target'].'" href="'.$item['link']['href'].'">'.$item['link']['text'].'</a>' : '';
    }
}

if(!function_exists('renderSystemTitle')){
    function renderSystemTitle(array $item = [], $systems = null){
        return (isset($item['title'])) ? '<span class="system-link text-danger">'.$item['title'].'</span>' : '';
    }
}

if(!function_exists('renderSystemSelect')){
    function renderSystemSelect(array $item, string $name = '', $systems = null){
       $html = '<select name="config['.$name.']" class="form-control">';
            foreach($item['option'] as $key => $val){
                $html .= '<option '.((isset($systems[$name]) && $key == $systems[$name]) ? 'selected' : '').' value="'.$key.'">'.$val.'</option>';
            }
       $html .= '</select>';

       return $html;
    }
}

if(!function_exists('cai_dat')){
    /**
     * Doc mot gia tri trong muc Cau hinh he thong.
     *
     * Bang systems luu theo tung ngon ngu. Nhung cai dat kieu bat/tat thi
     * khong phu thuoc ngon ngu, ma form quan tri lai luu no duoi ngon ngu
     * dang mo - nen doc ngon ngu hien tai truoc, khong thay thi lay bat ky
     * ngon ngu nao co khoa do.
     *
     * Khong nho ket qua lai: ham nay chi duoc goi mot hai lan moi request (luc
     * luu bai hoac luu du an), nho lai khong tiet kiem duoc gi ma lai lam ket
     * qua phu thuoc vao thu tu goi - quan tri vua doi cai dat xong van con doc
     * ra gia tri cu.
     */
    function cai_dat(string $keyword, $macDinh = null){
        try {
            $ngonNgu = \App\Models\Language::where('canonical', app()->getLocale())->value('id');

            $giaTri = \Illuminate\Support\Facades\DB::table('systems')
                ->where('keyword', $keyword)
                ->orderByRaw('language_id = ? DESC', [(int) $ngonNgu])
                ->value('content');
        } catch (\Throwable $e) {
            // Chua chay migration hoac mat ket noi CSDL thi tra ve mac dinh,
            // khong lam vo ca trang chi vi mot o cai dat.
            $giaTri = null;
        }

        return ($giaTri === null || $giaTri === '') ? $macDinh : $giaTri;
    }
}

if(!function_exists('write_url')){
    function write_url($canonical = null, bool $fullDomain = true, $suffix = true){
        $canonical = ($canonical) ?? '';
        if(strpos($canonical, 'http') !== false){
            return $canonical;
        }
        $fullUrl = (($fullDomain === true) ? config('app.url') : '').$canonical.( ($suffix === true) ? config('apps.general.suffix') : '' );
        return $fullUrl;
    }
}

if(!function_exists('seo')){
    function seo($model = null, $page = 1){
        $canonical = ($page > 1) ? write_url($model->canonical, true, false).'/trang-'.$page.config('apps.general.suffix'): write_url($model->canonical, true, true);
        return [
            'meta_title' => ($model->meta_title) ?? $model->name,
            'meta_keyword' => ($model->meta_keyword) ?? '',
            'meta_description' => ($model->meta_description) ?? cut_string_and_decode($model->descipriont, 168),
            'meta_image' => $model->image,
            'canonical' => $canonical,
        ];
    }
}

if(!function_exists('recursive')){
    function recursive($data, $parentId = 0){
        $temp = [];
        if(!is_null($data) && count($data)){
            foreach($data as $key => $val){
                if($val->parent_id == $parentId){
                    $temp[] = [
                        'item' => $val,
                        'children' => recursive($data, $val->id)
                    ];
                }
            }
        }
        return $temp;
    }
}

if(!function_exists('frontend_recursive_menu')){
    function frontend_recursive_menu(array $data = [], int $parentId = 0, int $count = 1, $type = 'html'){
        $html = '';
        if(isset($data) && !is_null($data) && count($data)){
            if($type == 'html'){
                foreach($data as $key => $val){
                    // Guard: bỏ qua menu item không có language (tránh lỗi pivot on null)
                    $firstLang = $val['item']->languages->first();
                    if (is_null($firstLang) || is_null($firstLang->pivot)) {
                        continue;
                    }
                    $name = $firstLang->pivot->name;
                    $canonical = write_url($firstLang->pivot->canonical, true, true);
                    $ulClass = ($count >= 1) ? 'menu-level__'.($count + 1) : '';
                    $html .= '<li class="'.(($count != 0 && count($val['children'])) ? 'children' : '').'">';
                        $html .= '<a href="'.(($name == 'Trang chủ') ? '.' : $canonical).'" title="'.$name.'" data-menu-id="'.$val['item']->id.'">'.
                        (($name == 'Home') ? '' : '').$name.'</a>';
                        if(count($val['children'])){
                            $html .= '<div class="dropdown-menu">';
                                $html .= '<ul class="uk-list uk-clearfix menu-style '.$ulClass.'">';
                                    $html .= frontend_recursive_menu($val['children'], $val['item']->parent_id,  $count + 1, $type);
                                $html .= '</ul>';
                            $html .='</div>';
                        }
                    $html .= '</li>';
                }
                return $html;
            } 
        }
        return ($type === 'html') ? '' : $data;
       
    }
}


if(!function_exists('recursive_menu')){
    function recursive_menu($data){
        $html = '';
        if(count($data)){
            foreach($data as $key => $val){
                $itemId = $val['item']->id;
                $firstLang = $val['item']->languages->first();
                $itemName = ($firstLang && $firstLang->pivot) ? $firstLang->pivot->name : '(No name)';
                $itemUrl = route('menu.children', ['id' => $itemId]);


                $html .= "<li class='dd-item' data-id='$itemId'>" ;
                    $html .= "<div class='dd-handle'>";
                        $html .= "<span class='label label-info'><i class='fa fa-arrows'></i></span> $itemName";
                    $html .= "</div>";
                    $html .= "<a class='create-children-menu' href='$itemUrl'> Quản lý menu con </a>";

                    if(count($val['children'])){
                        $html .= "<ol class='dd-list'>";
                            $html .= recursive_menu($val['children']);
                        $html .= '</ol>';
                    }
                $html .= "</li>";
            }
        }
        return $html;
    }
}


if(!function_exists('buildMenu')){
    function buildMenu($menus = null, $parent_id = 0, $prefix = ''){
        $output = [];
        $count = 1;

        if(count($menus)){
            foreach($menus as $key => $val){
                if($val->parent_id == $parent_id){
                    $val->position = $prefix.$count;
                    $output[] = $val;
                    $output = array_merge($output, buildMenu($menus, $val->id, $val->position . '.'));
                    $count++;
                }
            }
        }
        return $output;
    }
}

use Illuminate\Support\Str;
if(!function_exists('loadClass')){
    function loadClass(string $model = '', $folder = 'Repositories', $interface = 'Repository'){
        $serviceInstance = null;
        $namespace = Str::words(Str::headline($model), 1, '');
        $version2 = ['Scholar', 'School', 'Major', 'Admission'];
        if(in_array($namespace, $version2)){
            $interface = 'Repo';
        }
        // $serviceInterfaceNamespace = '\App\\'.$folder.'\\' . ucfirst($model) . $interface;
        $serviceInterfaceNamespace = '\App\\' . $folder . '\\' . $namespace . '\\' . $model . $interface;
        if (class_exists($serviceInterfaceNamespace)) {
            $serviceInstance = app($serviceInterfaceNamespace);
        }
        return $serviceInstance;
    }
}

if(!function_exists('convertArrayByKey')){
    function convertArrayByKey($object = null, $fields = []){
        $temp = [];
        foreach($object as $key => $val){
            foreach($fields as $field){
                if(is_array($object)){
                    $temp[$field][] = $val[$field];
                }else{
                    $extract = explode('.', $field);
                    if(count($extract) == 2){
                        if($extract[1] == 'languages'){
                            $temp[$extract[0]][] = $val->{$extract[1]}->first()->pivot->{$extract[0]};
                        }else{
                            $temp[$extract[0]][] = $val->pivot->{$extract[0]};
                        }
                        
                    }else{
                        $temp[$field][] = $val->{$field}; 
                    }
                    
                }
            }
        }
        return $temp;
    }
}

if(!function_exists('renderQuickBuy')){
    function renderQuickBuy($product, string $canonical = '', string $name = ''){

        $class = 'btn-addCart';
        $openModal = '';
        if(isset($product->product_variants) && count($product->product_variants)){
            $class = '';
            $canonical = '#popup';
            $openModal = 'data-uk-modal';
        }

        $html = '<a href="'.$canonical.'" '.$openModal.' title="'.$name.'" class="'.$class.'">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g>
                    <path d="M24.4941 3.36652H4.73614L4.69414 3.01552C4.60819 2.28593 4.25753 1.61325 3.70863 1.12499C3.15974 0.636739 2.45077 0.366858 1.71614 0.366516L0.494141 0.366516V2.36652H1.71614C1.96107 2.36655 2.19748 2.45647 2.38051 2.61923C2.56355 2.78199 2.68048 3.00626 2.70914 3.24952L4.29414 16.7175C4.38009 17.4471 4.73076 18.1198 5.27965 18.608C5.82855 19.0963 6.53751 19.3662 7.27214 19.3665H20.4941V17.3665H7.27214C7.02705 17.3665 6.79052 17.2764 6.60747 17.1134C6.42441 16.9505 6.30757 16.7259 6.27914 16.4825L6.14814 15.3665H22.3301L24.4941 3.36652ZM20.6581 13.3665H5.91314L4.97214 5.36652H22.1011L20.6581 13.3665Z" fill="#253D4E"></path>
                    <path d="M7.49414 24.3665C8.59871 24.3665 9.49414 23.4711 9.49414 22.3665C9.49414 21.2619 8.59871 20.3665 7.49414 20.3665C6.38957 20.3665 5.49414 21.2619 5.49414 22.3665C5.49414 23.4711 6.38957 24.3665 7.49414 24.3665Z" fill="#253D4E"></path>
                    <path d="M17.4941 24.3665C18.5987 24.3665 19.4941 23.4711 19.4941 22.3665C19.4941 21.2619 18.5987 20.3665 17.4941 20.3665C16.3896 20.3665 15.4941 21.2619 15.4941 22.3665C15.4941 23.4711 16.3896 24.3665 17.4941 24.3665Z" fill="#253D4E"></path>
                    </g>
                    <defs>
                    <clipPath>
                    <rect width="24" height="24" fill="white" transform="translate(0.494141 0.366516)"></rect>
                    </clipPath>
                    </defs>
                </svg>
        </a>';
    return $html;
    }
}

if(!function_exists('cutnchar')){
	function cutnchar($str = NULL, $n = 320){
		if(strlen($str) < $n) return $str;
		$html = substr($str, 0, $n);
		$html = substr($html, 0, strrpos($html,' '));
		return $html.'...';
	}
}

if(!function_exists('cut_string_and_decode')){
	function cut_string_and_decode($str = NULL, $n = 200){
        $str = html_entity_decode($str);
        $str = strip_tags($str);
        $str = cutnchar($str, $n);
        return $str;
	}
}

if(!function_exists('categorySelectRaw')){
    function categorySelectRaw($table = 'products'){
        $rawQuery = "
            (
                SELECT COUNT(id) 
                FROM {$table}s
                JOIN {$table}_catalogue_{$table} as tb3 ON tb3.{$table}_id = {$table}s.id
                WHERE tb3.{$table}_catalogue_id IN (
                    SELECT id
                    FROM {$table}_catalogues as parent_category
                    WHERE lft >= (SELECT lft FROM {$table}_catalogues as pc WHERE pc.id = {$table}_catalogues.id)
                    AND rgt <= (SELECT rgt FROM {$table}_catalogues as pc WHERE pc.id = {$table}_catalogues.id)
                )
            ) as {$table}s_count 
        "; 
        return $rawQuery;
    }
}


if(!function_exists('sortString')){
    function sortString($string = ''){
        $extract = explode(',', $string);
        $extract = array_map('trim', $extract);
        sort($extract, SORT_NUMERIC);
        $newArray = implode(',', $extract);
        return $newArray;
    }
}


if(!function_exists('sortAttributeId')){
    function sortAttributeId(array $attributeId = []){
        sort($attributeId, SORT_NUMERIC);
        $attributeId = implode(',', $attributeId);
        return $attributeId;
    }
}


if(!function_exists('vnpayConfig')){
    function vnpayConfig(){
        return [
            'vnp_Url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'vnp_Returnurl' => write_url('return/vnpay'),
            'vnp_TmnCode' => 'RLE42FCR',
            'vnp_HashSecret' => 'OQPUUZRVSSJASOQVUQHHURHBXGDIMBTU',
            'vnp_apiUrl' => 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html',
            'apiUrl' => 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'
        ];
    }
}


if(!function_exists('momoConfig')){
    function momoConfig(){
        return [
            'partnerCode' => 'MOMOBKUN20180529',
            'accessKey' => 'klm05TvNBzhg7h7j',
            'secretKey' => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
        ];
    }
}

if(!function_exists('zaloConfig')){
    function zaloConfig(){
        return [
            'appid' => '553',
            'key1' => '9phuAOYhan4urywHTh0ndEXiV3pKHr5Q',
            'key2' => 'Iyz2habzyr7AG8SgvoBCbKwKi3UzlLi3',
        ];
    }
}

if(!function_exists('execPostRequest')){
    function execPostRequest($url, $data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
}


if(!function_exists('getReviewName')){
    function getReviewName($string){
        // $string = Nguyễn Công Tuấn
        $words = explode(' ', $string);
        $initialize = '';
        foreach($words as $key => $val){
            $initialize .= strtoupper(substr($val, 0, 1));
        }
        return $initialize;
    }
}


if(!function_exists('generateStar')){
    function generateStar($rating){
        $rating = max(1, min(5, $rating));
        $output = '<div class="review-star">';
            for($i = 1; $i <= $rating; $i++){
                $output .= '<i class="fa fa-star"></i>';
            }
            for($i = $rating + 1; $i <= 5; $i++){
                $output .= '<i class="fa fa-star-o"></i>';
            }
        $output .= '</div>';
        return $output;
    }
}


if(!function_exists('convertCombineArray')){
    function convertCombineArray(mixed $data, $mix_1 = ''){
        $array = [];
        foreach($data as $key => $val){
            $array[$val->id] = (($mix_1 != '') ? $val->{$mix_1} : $val->code).' / '.$val->phone;
        }
        return $array;
    }
}


if(!function_exists('convertArray')){
    function convertArray($datas){
        $id = [];
        foreach ($datas as $data) {
            $id[]= $data->id;
        }
        return $id;
    }
}

if(!function_exists('convertToIdNameArray')){
    function convertToIdNameArray($customers)
   {
    $idNameArray = [];

    foreach ($customers as $customer) {
        $idNameArray[$customer['id']] = $customer['name'];
    }

    return $idNameArray;
    }

}

if(!function_exists('convertToK')){
    function convertToK($discount)
   {
        if ($discount >= 1000) {
            return number_format($discount / 1000, 0, '.', '') . 'k';
        }
        return $discount;
    }
}
  
use Illuminate\Support\Facades\DB;
if(!function_exists('convertData')){
    function convertData($data, $type)
    {
        $promotion_id = $data->id;
        $payload_pivot = ($type == 'products') ? $data->promotion_rules : $data->promotion_gifts;
        $products = ($type == 'products') ? 
        DB::table('promotion_rules')->where('promotion_id', $promotion_id)->get() : DB::table('promotion_gifts')->where('promotion_id', $promotion_id)->get();
        $temp = [];
        if(!is_null($products)){
            foreach($products as $k => $v){
                $temp['id'][$k] = $v->product_id;
                $temp['quantity'][$k] = $v->quantity;
                $temp['image'][$k] = $payload_pivot[$k]['image'];
                $temp['name'][$k] = $payload_pivot[$k]->languages->first()->pivot->name;
            }
        }
        return $temp;
    }
}



if(!function_exists('thumb')){
    function thumb($path, $width = null, $height = null)
    {
        $width = 600;
        $height = 400;

        if (empty($path)) {
            return asset('images/no-image.jpg');
        }
        
        $params = ['src' => $path];
        
        if ($width) {
            $params['w'] = $width;
        }
        
        if ($height) {
            $params['h'] = $height;
        }
        
        // return route('thumb', $params);

        return $path;
    }
}

if (!function_exists('convertImgToAnchor')) {
    function convertImgToAnchor($html) {
        if (!$html || !is_string($html)) {
            return $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        if ($images->length === 0) {
            return $html;
        }

        foreach ($images as $image) {
            $src = $image->getAttribute('src') ?: '';
            $alt = $image->getAttribute('alt') ?: '';
            $class = $image->getAttribute('class') ?: 'img-cover img-zoomin';

            // Tạo thẻ <a>
            $anchor = $dom->createElement('a');
            $anchor->setAttribute('href', $src);
            $anchor->setAttribute('title', $alt);
            $anchor->setAttribute('class', $class);

            // Thêm <div class="skeleton-loading"> vào trong <a>
            $skeleton = $dom->createElement('span');
            $skeleton->setAttribute('class', 'skeleton-loading');
            $anchor->appendChild($skeleton);

            // Thêm <img class="lazy-image"> vào trong <a>
            $newImg = $dom->createElement('img');
            $newImg->setAttribute('class', 'lazy-image');
            $newImg->setAttribute('data-src', $src);
            $newImg->setAttribute('alt', $alt);
            $anchor->appendChild($newImg);

            // Thay thế <img> bằng <a> hoàn chỉnh
            $image->parentNode->replaceChild($anchor, $image);
        }

        $html = $dom->saveHTML();
        // Loại bỏ các thẻ HTML bổ sung do DOMDocument thêm vào
        $html = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(['<html><body>', '</body></html>'], '', $html));

        return $html;
    }
}

if (!function_exists('calculateCourses')) {
    function calculateCourses($product) {
        $totalMinutes = 0;
        $totalSession = 0;
        $temp = $product->chapter;
        if (!is_array($temp)) {
            $temp = json_decode($temp, true); 
        }
        foreach ($temp as $chapter) {
            if (isset($chapter['content']) && is_array($chapter['content'])) {
                foreach ($chapter['content'] as $lesson) {
                    $totalSession++;
                    if (isset($lesson['time'])) {
                        $totalMinutes += (int)$lesson['time'];
                    }
                }
            }
        }
        
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        $durationText = '';
        if ($hours > 0 && $minutes > 0) {
            $durationText = $hours . ' giờ ' . $minutes . ' phút';
        } elseif ($hours > 0) {
            $durationText = $hours . ' giờ';
        } else {
            $durationText = $minutes . ' phút';
        }
        $chapters = [
            'durationText' => $durationText,
            'totalSession' => $totalSession
        ];
        return $chapters;
    }
}
/*
|--------------------------------------------------------------------------
| Ham dung rieng cho NOXH.vn
|--------------------------------------------------------------------------
*/

if (!function_exists('so_gon')) {
    /**
     * In mot so thap phan theo kieu Viet Nam, bo phan le khi bang khong.
     * 19.55 -> "19,55"   32.00 -> "32"   null -> ""
     *
     * $soLe: gia can ho tinh bang ty can BA chu so le ("1,075 ty"), lam tron
     * hai chu so la mat 5 trieu dong ma nguoi doc khong he biet.
     */
    function so_gon($so, int $soLe = 2): string
    {
        if ($so === null || $so === '') {
            return '';
        }

        $so = (float) $so;
        $chuoi = number_format($so, $soLe, ',', '.');

        return rtrim(rtrim($chuoi, '0'), ',');
    }
}

if (!function_exists('khoang_so')) {
    /**
     * Gop hai dau thanh mot khoang: "32 - 70 m2".
     *
     * Thieu mot dau thi in dau con lai kem tu "Tu"/"Den" chu khong in dau
     * gach cut lung.
     *
     * $khiTrong: thieu ca hai dau thi tra ve chuoi nay. Mac dinh la dau gach,
     * vi the du an de o trong se vo bo cuc; nhung cho nao muon TU AN khoi di
     * (vi du the gia o trang chi tiet) thi truyen '' de con phan biet duoc.
     *
     * $soLe: so chu so thap phan, xem so_gon().
     */
    function khoang_so($tu, $den, string $donVi = '', string $khiTrong = '—', int $soLe = 2): string
    {
        $a = so_gon($tu, $soLe);
        $b = so_gon($den, $soLe);

        if ($a !== '' && $b !== '' && $a !== $b) {
            // Hai dau CUNG CO phan thap phan thi phai cung so chu so le.
            // so_gon() cat so 0 cuoi cua tung so rieng le nen "1,075 - 1,180"
            // se thanh "1,075 - 1,18", nhin nhu mot loi danh may.
            //
            // Mot dau tron (21) thi de nguyen - ban thiet ke viet "19,55 - 21 m2"
            // chu khong phai "19,55 - 21,00 m2".
            $leA = strlen((string) strstr($a, ',')) - 1;
            $leB = strlen((string) strstr($b, ',')) - 1;

            if ($leA > 0 && $leB > 0 && $leA !== $leB) {
                $le = max($leA, $leB);
                $a = number_format((float) $tu, $le, ',', '.');
                $b = number_format((float) $den, $le, ',', '.');
            }

            // Gach NGANG (en dash) chu khong phai dau tru: ban thiet ke viet
            // "32 - 70 m2" bang gach ngang, va dau tru dung giua hai so
            // trong nhu phep tinh.
            return $a . ' – ' . $b . $donVi;
        }

        if ($a !== '') {
            return $a . $donVi;
        }

        if ($b !== '') {
            return $b . $donVi;
        }

        return $khiTrong;
    }
}

if (!function_exists('khoang_gia')) {
    /** Khoang gia ban, khong kem don vi (don vi in rieng cho nho hon). */
    function khoang_gia($tu, $den): string
    {
        return khoang_so($tu, $den);
    }
}

if (!function_exists('tien_viet')) {
    /**
     * Doi mot so tien (don vi dong) ra cach viet quen thuoc.
     *
     *     20000000    -> "20 trieu"
     *     25500000    -> "25,5 trieu"
     *     1250000000  -> "1,25 ty"
     *     850000      -> "850 nghin"
     *
     * Du an bat dong san co con so rat lon, viet day du chu so thi nguoi doc
     * phai dem hang moi biet la bao nhieu. Nguoc lai lam tron qua tay thi
     * 1,04 ty va 1,4 ty trong giong nhau - nen giu toi hai chu so thap phan
     * voi ty va mot voi trieu.
     *
     * @param  float|int|string|null  $dong    So tien, don vi dong
     * @param  bool                   $ngan    true thi viet tat: "25tr", "1,2ty"
     */
    function tien_viet($dong, bool $ngan = false): string
    {
        if ($dong === null || $dong === '') {
            return '';
        }

        $dong = (float) $dong;
        $am = $dong < 0;
        $dong = abs($dong);

        [$chia, $donVi, $soLe] = match (true) {
            $dong >= 1000000000 => [1000000000, $ngan ? 'ty' : 'tỷ', 2],
            $dong >= 1000000 => [1000000, $ngan ? 'tr' : 'triệu', 1],
            $dong >= 1000 => [1000, $ngan ? 'k' : 'nghìn', 0],
            default => [1, 'đồng', 0],
        };

        $giaTri = $dong / $chia;

        $so = number_format($giaTri, $soLe, ',', '.');

        // Bo phan thap phan bang 0: "20,0 trieu" doc nang ne hon "20 trieu".
        // Chi lam khi thuc su CO dau phay - khong thi 850.000 se thanh
        // "85 nghin" vi so 0 cuoi cua phan nguyen cung bi cat.
        if (str_contains($so, ',')) {
            $so = rtrim(rtrim($so, '0'), ',');
        }

        // "dong" khong co dang viet tat, giu nguyen ca khoang trang.
        $vietTat = $ngan && $donVi !== 'đồng';

        return ($am ? '-' : '') . $so . ($vietTat ? '' : ' ') . $donVi;
    }
}

if (!function_exists('khoang_tien')) {
    /**
     * Mot khoang tien: 1075000000 va 1180000000 -> "1,08 - 1,18 ty".
     *
     * Chi in don vi mot lan o cuoi khi hai dau cung don vi - "1,08 ty - 1,18
     * ty" dai ma khong ro hon.
     */
    function khoang_tien($tu, $den): string
    {
        $tu = ($tu === null || $tu === '') ? null : (float) $tu;
        $den = ($den === null || $den === '') ? null : (float) $den;

        if ($tu === null && $den === null) {
            return '';
        }

        if ($tu === null || $den === null || abs($tu - (float) $den) < 0.01) {
            return tien_viet($tu ?? $den);
        }

        $chuTu = tien_viet($tu);
        $chuDen = tien_viet($den);

        $donViTu = trim(strrchr($chuTu, ' ') ?: '');
        $donViDen = trim(strrchr($chuDen, ' ') ?: '');

        if ($donViTu === $donViDen) {
            return trim(str_replace(' ' . $donViTu, '', $chuTu)) . ' - ' . $chuDen;
        }

        return $chuTu . ' - ' . $chuDen;
    }
}

if (!function_exists('dung_luong')) {
    /**
     * Doi so byte ra chu: 1536000 -> "1,5 MB".
     *
     * Dung cho file van ban tai len - nguoi dung can biet file nang bao nhieu
     * truoc khi bam tai ve, nhat la khi dung 3G.
     */
    function dung_luong($byte): string
    {
        $byte = (float) $byte;

        if ($byte <= 0) {
            return '';
        }

        $donVi = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($byte >= 1024 && $i < count($donVi) - 1) {
            $byte /= 1024;
            $i++;
        }

        $soLe = $i === 0 ? 0 : 1;
        $so = number_format($byte, $soLe, ',', '.');

        // Chi cat so 0 thua khi co dau phay - khong thi 120 B thanh "12 B".
        if (str_contains($so, ',')) {
            $so = rtrim(rtrim($so, '0'), ',');
        }

        return $so . ' ' . $donVi[$i];
    }
}

if (!function_exists('nx_url')) {
    /**
     * Duong dan trong website NOXH. Nhan ca chuoi rong (ve trang chu).
     */
    function nx_url(?string $duongDan = ''): string
    {
        $duongDan = trim((string) $duongDan, '/');

        return $duongDan === '' ? url('/') : url('/' . $duongDan);
    }
}

if (!function_exists('nx_hotline_dau')) {
    /**
     * So dien thoai dau tien trong o "Hotline" cua Cau hinh he thong.
     *
     * O do cho phep ghi nhieu so cach nhau bang dau gach dung, vi du
     * "0989 591 616 | 0942 141 686". Dau trang va chan trang chi du cho mot
     * so nen lay so dau.
     */
    function nx_hotline_dau(?string $hotline): string
    {
        foreach (explode('|', (string) $hotline) as $so) {
            $so = trim($so);

            if ($so !== '') {
                return $so;
            }
        }

        return '';
    }
}

if (!function_exists('nx_mau_chuyen_muc')) {
    /**
     * Ma mau cua mot chuyen muc tin tuc, lay tu o "Mau nhan" trong quan tri.
     *
     * Tra ve chuoi rong neu quan tri chua chon mau - noi goi se tu dung mau
     * xanh mac dinh. Chi nhan dang #rgb va #rrggbb de gia tri tu CSDL khong
     * chen duoc gi khac vao thuoc tinh style.
     */
    function nx_mau_chuyen_muc(?string $mau): string
    {
        $mau = trim((string) $mau);

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $mau) ? $mau : '';
    }
}

if (!function_exists('nx_khoang_gia')) {
    /**
     * Tach o "Cac khoang gia" cua module Gioi thieu thanh danh sach lua chon.
     *
     * Quan tri go moi dong mot khoang theo dang  Nhan | tu-den , vi du:
     *
     *     Dưới 18 triệu/m²  | -18
     *     18 - 20 triệu/m²  | 18-20
     *     Trên 22 triệu/m²  | 22-
     *
     * De trong mot dau nghia la khong gioi han ben do. Dong sai dinh dang thi
     * bo qua - mot dong go nham khong duoc lam vo ca o chon.
     */
    function nx_khoang_gia(?string $vanBan): array
    {
        $ket = [];

        foreach (preg_split('/\R/', (string) $vanBan) as $dong) {
            $dong = trim($dong);

            if ($dong === '' || !str_contains($dong, '|')) {
                continue;
            }

            [$nhan, $khoang] = array_map('trim', explode('|', $dong, 2));

            // Chi nhan dang so-so, vi du "18-20", "-18", "22-".
            if ($nhan === '' || !preg_match('/^\d*(?:[.,]\d+)?-\d*(?:[.,]\d+)?$/', $khoang)) {
                continue;
            }

            $ket[] = ['label' => $nhan, 'value' => str_replace(',', '.', $khoang)];
        }

        return $ket;
    }
}

if (!function_exists('nx_ten_dia_gioi_ngan')) {
    /**
     * Bo tien to loai don vi hanh chinh o dau ten.
     *
     * API tra ve ten day du ("Tinh Thai Nguyen", "Thanh pho Ha Noi",
     * "Phuong Duc Xuan") - dung cho o chon va van ban hanh chinh. Nhung o
     * the loc hay the du an thi ban thiet ke chi de ten ngan ("Thai Nguyen",
     * "Ha Noi"), neu khong mot hang the chi vua duoc ba cai.
     */
    function nx_ten_dia_gioi_ngan(?string $ten): string
    {
        $ten = trim((string) $ten);

        if ($ten === '') {
            return '';
        }

        // Dat "Thanh pho truc thuoc trung uong" truoc "Thanh pho" - preg_replace
        // lay mau dau tien khop nen mau dai phai dung truoc.
        $bo = [
            'Thành phố trực thuộc trung ương',
            'Thành phố',
            'Tỉnh',
            'Đặc khu',
            'Phường',
            'Thị trấn',
            'Xã',
        ];

        foreach ($bo as $tienTo) {
            if (mb_strpos($ten, $tienTo . ' ') === 0) {
                return trim(mb_substr($ten, mb_strlen($tienTo) + 1));
            }
        }

        return $ten;
    }
}

if (!function_exists('nx_anh')) {
    /**
     * Duong dan anh, kem anh thay the khi ban ghi chua co anh.
     *
     * De trong o anh thi ngoai trang hien mot o xam rong, nhin nhu trang bi
     * loi. Moi loai noi dung co mot anh thay the rieng - quan tri tai anh
     * that len la anh do tu nhuong cho, khong phai sua gi.
     *
     * Anh thay the de trong public/images/noxh/ chu KHONG phai
     * public/uploads/: thu muc uploads nam trong .gitignore (anh nguoi dung
     * tai len), de o do thi clone ve may chu la mat sach, dung luc can lai
     * hien o vo anh - dung cai loi dang di chua.
     *
     * $loai: 'du-an' | 'du-an-doc' | 'tin-tuc' | 'dang-ky' | 'avatar'
     */
    function nx_anh(?string $anh, string $loai = 'du-an'): string
    {
        $anh = trim((string) $anh);

        if ($anh !== '') {
            // Bang cu luu duong dan co tien to /public/ - bo di cho dung.
            return str_replace('/public/', '/', $anh);
        }

        $thayThe = [
            'du-an' => '/images/noxh/du-an-mac-dinh-the.jpg',
            'du-an-doc' => '/images/noxh/du-an-mac-dinh-doc.jpg',
            'tin-tuc' => '/images/noxh/tin-tuc-mac-dinh.svg',
            'dang-ky' => '/images/noxh/dang-ky-mac-dinh.svg',
            'avatar' => '/images/noxh/avatar-mac-dinh.png',
        'tu-van' => '/images/noxh/tu-van-vien.svg',
        ];

        return $thayThe[$loai] ?? $thayThe['du-an'];
    }
}

if (!function_exists('nx_chu_dau')) {
    /**
     * Hai chu cai dau cua ten nguoi, dung lam anh dai dien khi chua co anh.
     *
     * Ten Viet Nam de ho truoc ten sau nen lay chu cai cua tu DAU va tu
     * CUOI: "Nguyen Van Hung" -> "NH". Mot tu thi lay mot chu.
     */
    function nx_chu_dau(?string $ten): string
    {
        $tu = preg_split('/\s+/u', trim((string) $ten), -1, PREG_SPLIT_NO_EMPTY);

        if (!$tu) {
            return '?';
        }

        $dau = mb_strtoupper(mb_substr($tu[0], 0, 1));

        if (count($tu) === 1) {
            return $dau;
        }

        return $dau . mb_strtoupper(mb_substr(end($tu), 0, 1));
    }
}

if (!function_exists('nx_mau_tu_ten')) {
    /**
     * Mau nen cua anh dai dien chu cai, chon theo ten.
     *
     * Cung mot nguoi luon ra cung mot mau (crc32 cua ten), nen danh sach
     * nhan vien khong nhay mau moi lan mo trang. Bay mau deu nam trong ho
     * mau cua website, khong co mau choi.
     */
    function nx_mau_tu_ten(?string $ten): string
    {
        $mau = ['#0a78f5', '#1e93cf', '#3e8c4a', '#7a5cd0', '#d4662a', '#0f6fa8', '#b8477e'];

        return $mau[crc32(mb_strtolower(trim((string) $ten))) % count($mau)];
    }
}

if (!function_exists('nx_ban_do_khung')) {
    /**
     * Khung toa do cua hinh ban do Viet Nam trong component vn-map.
     *
     * Bon so nay PHAI khop voi hinh ve trong
     * resources/views/frontend/noxh/component/vn-map.blade.php: duong bien
     * va ghim deu chieu bang cung mot phep tinh, doi mot so ma khong doi hinh
     * thi ghim se lech ra bien.
     *
     * [kinh do trai, kinh do phai, vi do tren, vi do duoi, rong, cao]
     */
    function nx_ban_do_khung(): array
    {
        return [
            'lng_min' => 101.90,
            'lng_max' => 109.90,
            'lat_max' => 23.60,
            'lat_min' => 8.30,
            'rong' => 1000.0,
            'cao' => 1565.0,
        ];
    }
}

if (!function_exists('nx_ban_do_diem')) {
    /**
     * Doi (vi do, kinh do) thanh toa do trong hinh ban do Viet Nam.
     *
     * Phep chieu la hinh chu nhat phang (equirectangular): dung cho mot
     * nuoc hep ngang nhu Viet Nam thi sai so nho hon nua ghim, va khong
     * phai keo thu vien ban do nao ve.
     *
     * Tra ve null neu thieu toa do hoac diem nam ngoai khung - goi ben ngoai
     * phai bo qua, dung ve ghim de tranh ghim dinh vao mep hinh.
     */
    function nx_ban_do_diem($lat, $lng): ?array
    {
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;
        $k = nx_ban_do_khung();

        if ($lng < $k['lng_min'] || $lng > $k['lng_max'] || $lat < $k['lat_min'] || $lat > $k['lat_max']) {
            return null;
        }

        return [
            'x' => round(($lng - $k['lng_min']) / ($k['lng_max'] - $k['lng_min']) * $k['rong'], 1),
            'y' => round(($k['lat_max'] - $lat) / ($k['lat_max'] - $k['lat_min']) * $k['cao'], 1),
        ];
    }
}

if (!function_exists('nx_quy_nam')) {
    /**
     * Doi mot ngay thanh "Quy IV/2026".
     *
     * Du an bat dong san cong bo moc thoi gian theo quy chu khong theo ngay:
     * in ra "15/10/2026" la sai voi cach nguoi mua doc. Quan tri van nhap
     * ngay (de con sap xep duoc), trang tu quy doi khi hien.
     */
    function nx_quy_nam($ngay): string
    {
        if (empty($ngay)) {
            return '';
        }

        try {
            $d = \Illuminate\Support\Carbon::parse($ngay);
        } catch (\Exception $e) {
            return '';
        }

        $so = ['I', 'II', 'III', 'IV'][intdiv($d->month - 1, 3)];

        return 'Quý ' . $so . '/' . $d->year;
    }
}

if (!function_exists('so_rut_gon')) {
    /**
     * Rut gon so luot xem theo kieu ban thiet ke: 5200 -> "5,2K", 1200000 ->
     * "1,2M". Duoi mot nghin thi in nguyen so, vi "0,8K" kho doc hon "800".
     */
    function so_rut_gon($so): string
    {
        $so = (int) $so;

        foreach ([1000000 => 'M', 1000 => 'K'] as $moc => $chu) {
            if ($so >= $moc) {
                return rtrim(rtrim(number_format($so / $moc, 1, ',', '.'), '0'), ',') . $chu;
            }
        }

        return number_format($so, 0, ',', '.');
    }
}
