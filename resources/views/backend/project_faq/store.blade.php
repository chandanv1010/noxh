@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('project.faq.store')
        : route('project.faq.update', $faq->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-8">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc dự án <span class="text-danger">(*)</span></label>
                                    <select name="product_id" class="form-control setupSelect2">
                                        <option value="">[Chọn dự án]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('product_id', ($faq->product_id) ?? request('product_id')) === (int) $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($faq->order) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Câu hỏi <span class="text-danger">(*)</span></label>
                                    <textarea name="question" rows="3" class="form-control">{{ old('question', ($faq->question) ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Câu trả lời</label>
                                    <textarea name="answer" class="ck-editor" id="ck_answer" data-height="220">{{ old('answer', ($faq->answer) ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($faq->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($faq->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('backend.dashboard.component.button')
    </div>
</form>
