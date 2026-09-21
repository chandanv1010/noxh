<?php
/*
|--------------------------------------------------------------------------
| Thanh menu ben trai cua trang quan tri NOXH.vn
|--------------------------------------------------------------------------
|
| Cac module cua ban clone truc ma NOXH khong dung (don hang, khuyen mai,
| voucher, nguon khach, nhom khach hang, binh luan, banner/slide, nha phan
| phoi) da duoc go khoi menu.
|
| Ma nguon va bang du lieu cua chung VAN CON - chi la khong hien ra menu nua.
| Muon bat lai thi them lai muc tuong ung o day.
|
*/
return [
    'module' => [
        [
            'title' => 'Dashboard',
            'icon' => 'fa fa-database',
            'name' => ['dashboard'],
            'route' => 'dashboard/index',
            'class' => 'special'
        ],

        // --- Du an --------------------------------------------------------
        //
        // Du an NOXH dung chinh module San pham: bang products da duoc bo sung
        // cac cot rieng (vi tri, khoang gia, khoang dien tich, quy mo...).
        [
            'title' => 'QL Dự án',
            'icon' => 'fa fa-cube',
            'name' => ['product', 'attribute'],
            'subModule' => [
                [
                    'title' => 'Nhóm dự án',
                    'route' => 'product/catalogue/index'
                ],
                [
                    'title' => 'Danh sách dự án',
                    'route' => 'product/index'
                ],
                [
                    'title' => 'Nhóm thuộc tính (bộ lọc)',
                    'route' => 'attribute/catalogue/index'
                ],
                [
                    'title' => 'Thuộc tính',
                    'route' => 'attribute/index'
                ],
            ]
        ],
        [
            'title' => 'QL Dự án NOXH',
            'icon' => 'fa fa-building',
            'name' => ['investor', 'project'],
            'subModule' => [
                [
                    'title' => 'Chủ đầu tư',
                    'route' => 'investor/index'
                ],
                [
                    'title' => 'Tiến độ dự án',
                    'route' => 'project/milestone/index'
                ],
                [
                    'title' => 'Hồ sơ pháp lý dự án',
                    'route' => 'project/document/index'
                ],
                [
                    'title' => 'Câu hỏi thường gặp',
                    'route' => 'project/faq/index'
                ],
            ]
        ],

        // --- Cac module rieng cua NOXH ------------------------------------
        [
            'title' => 'QL Kiểm tra điều kiện',
            'icon' => 'fa fa-check-square-o',
            'name' => ['eligibility'],
            'subModule' => [
                [
                    'title' => 'Bộ câu hỏi',
                    'route' => 'eligibility/question/index'
                ],
                [
                    'title' => 'Đáp án',
                    'route' => 'eligibility/option/index'
                ],
                [
                    'title' => 'Kết quả khách đã kiểm tra',
                    'route' => 'eligibility/check/index'
                ],
            ]
        ],
        [
            'title' => 'QL Phòng pháp lý',
            'icon' => 'fa fa-gavel',
            'name' => ['legal-document', 'qa'],
            'subModule' => [
                [
                    'title' => 'Văn bản pháp luật',
                    'route' => 'legal-document/index'
                ],
                [
                    'title' => 'Hỏi đáp',
                    'route' => 'qa/question/index'
                ],
            ]
        ],
        [
            'title' => 'QL Hồ sơ',
            'icon' => 'fa fa-folder-open-o',
            'name' => ['dossier'],
            'subModule' => [
                [
                    'title' => 'Bộ hồ sơ',
                    'route' => 'dossier/set/index'
                ],
                [
                    'title' => 'Giấy tờ',
                    'route' => 'dossier/item/index'
                ],
            ]
        ],
        [
            'title' => 'QL Tài chính',
            'icon' => 'fa fa-calculator',
            'name' => ['loan-package'],
            'subModule' => [
                [
                    'title' => 'Gói vay ngân hàng',
                    'route' => 'loan-package/index'
                ],
            ]
        ],
        [
            'title' => 'QL Chuyên gia',
            'icon' => 'fa fa-user-md',
            'name' => ['expert'],
            'subModule' => [
                [
                    'title' => 'Chuyên gia tư vấn',
                    'route' => 'expert/index'
                ],
            ]
        ],

        // --- Noi dung -----------------------------------------------------
        [
            'title' => 'QL Tin tức',
            'icon' => 'fa fa-newspaper-o',
            'name' => ['post'],
            'subModule' => [
                [
                    'title' => 'Chuyên mục',
                    'route' => 'post/catalogue/index'
                ],
                [
                    'title' => 'Bài viết',
                    'route' => 'post/index'
                ],
            ]
        ],
        [
            'title' => 'Nội dung trang',
            'icon' => 'fa fa-info-circle',
            'name' => ['introduce'],
            'subModule' => [
                [
                    'title' => 'Chữ trên trang chủ & trang trong',
                    'route' => 'introduce/index'
                ]
            ]
        ],
        [
            'title' => 'QL Liên hệ',
            'icon' => 'fa fa-envelope-o',
            'name' => ['contact'],
            'subModule' => [
                [
                    'title' => 'Thông tin khách để lại',
                    'route' => 'contact/index'
                ],
            ]
        ],

        // --- He thong -----------------------------------------------------
        [
            'title' => 'QL Menu',
            'icon' => 'fa fa-bars',
            'name' => ['menu'],
            'subModule' => [
                [
                    'title' => 'Cài đặt Menu',
                    'route' => 'menu/index'
                ],
            ]
        ],
        [
            'title' => 'QL Thành viên',
            'icon' => 'fa fa-users',
            'name' => ['user', 'permission'],
            'subModule' => [
                [
                    'title' => 'Nhóm thành viên',
                    'route' => 'user/catalogue/index'
                ],
                [
                    'title' => 'Thành viên',
                    'route' => 'user/index'
                ],
                [
                    'title' => 'Quyền',
                    'route' => 'permission/index'
                ],
            ]
        ],
        [
            'title' => 'Cấu hình chung',
            'icon' => 'fa fa-cog',
            'name' => ['language', 'generate', 'system', 'widget'],
            'subModule' => [
                [
                    'title' => 'Cấu hình hệ thống',
                    'route' => 'system/index'
                ],
                [
                    'title' => 'QL Ngôn ngữ',
                    'route' => 'language/index'
                ],
            ]
        ]
    ],
];
