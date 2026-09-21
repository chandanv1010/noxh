<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\QaQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QaController extends FrontendController
{
    public function index(Request $request)
    {
        $query = QaQuestion::withCount('answers')->where('publish', 2);

        if ($tu = trim((string) $request->input('tu-khoa'))) {
            $query->where('title', 'LIKE', '%' . $tu . '%');
        }

        return view('frontend.noxh.qa.index', [
            'system' => $this->system,
            'seo' => $this->seo('Hỏi đáp về nhà ở xã hội', url('/hoi-dap')),
            'cauHoi' => $query->orderByDesc('is_featured')->orderByDesc('id')->paginate(12)->withQueryString(),
            'noiBat' => QaQuestion::where('publish', 2)->where('is_featured', 1)->limit(5)->get(),
        ]);
    }

    public function show(int $id)
    {
        $cauHoi = QaQuestion::with(['answers' => function ($q) {
            $q->where('publish', 2)->with('expert')->orderBy('id');
        }])->where('publish', 2)->find($id);

        if (!$cauHoi) {
            abort(404);
        }

        // Tang luot xem truc tiep tren CSDL de hai nguoi xem cung luc khong
        // de len nhau.
        QaQuestion::where('id', $cauHoi->id)->increment('view_count');

        return view('frontend.noxh.qa.show', [
            'system' => $this->system,
            'seo' => $this->seo($cauHoi->title, url('/hoi-dap/' . $cauHoi->id)),
            'cauHoi' => $cauHoi,
            'lienQuan' => QaQuestion::where('publish', 2)
                ->where('id', '!=', $cauHoi->id)
                ->orderByDesc('is_featured')
                ->limit(5)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'title' => 'required|string|max:500',
                'content' => 'nullable|string|max:5000',
                'asker_name' => 'required|string|max:191',
                'asker_phone' => 'nullable|string|max:20',
                'asker_email' => 'nullable|email|max:191',
            ],
            [
                'title.required' => 'Bạn chưa nhập câu hỏi.',
                'asker_name.required' => 'Bạn chưa nhập họ tên.',
                'asker_email.email' => 'Email không hợp lệ.',
            ]
        );

        QaQuestion::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'asker_name' => $request->input('asker_name'),
            'asker_phone' => $request->input('asker_phone'),
            'asker_email' => $request->input('asker_email'),
            'status' => 'pending',
            // Mac dinh AN: cau hoi phai duoc duyet trong quan tri moi hien ra
            // ngoai, khong de khach dang thang len trang.
            'publish' => 1,
        ]);

        return back()->with('nx_success', 'Đã nhận câu hỏi của bạn. Chuyên gia sẽ trả lời và đăng lên trong thời gian sớm nhất.');
    }

    private function seo(string $tieuDe, string $canonical, string $moTa = ''): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $moTa ?: ($this->system['seo_meta_description'] ?? ''),
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
