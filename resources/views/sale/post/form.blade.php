@php
    $duong = $cachLam === 'create'
        ? route('sale.post.store')
        : route('sale.post.update', $post->id);
@endphp

<form action="{{ $duong }}" method="post" class="box">
    @csrf

    <div class="row">
        <div class="col-lg-9">
            <div class="ibox">
                <div class="ibox-title"><h5>Nội dung bài viết</h5></div>
                <div class="ibox-content">
                    @include('backend.product.product.component.content', ['model' => $post])
                </div>
            </div>

            @include('backend.dashboard.component.seo', ['model' => $post])
        </div>

        <div class="col-lg-3">
            <div class="ibox">
                <div class="ibox-title"><h5>Chuyên mục</h5></div>
                <div class="ibox-content">
                    <div class="form-row">
                        <select name="post_catalogue_id" class="form-control setupSelect2">
                            @foreach($dropdown as $ma => $ten)
                                <option value="{{ $ma }}"
                                    {{ (int) old('post_catalogue_id', $post->post_catalogue_id ?? 0) === (int) $ma ? 'selected' : '' }}>
                                    {{ $ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title"><h5>Ảnh đại diện</h5></div>
                <div class="ibox-content">
                    <div class="form-row">
                        <div class="image-wrapper">
                            <input type="text" name="image" value="{{ old('image', $post->image ?? '') }}"
                                   class="form-control" placeholder="Đường dẫn ảnh">
                        </div>
                        @if(!empty($post->image))
                            <img src="{{ $post->image }}" alt="" style="margin-top:10px;max-width:100%">
                        @endif
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <p class="text-muted" style="font-size:13px;margin:0">
                        <i class="fa fa-info-circle"></i>
                        @if(cai_dat('sale_post_approval', 'on') === 'off')
                            Bài sẽ <strong>hiển thị ngay</strong> sau khi lưu.
                        @else
                            Bài sẽ ở trạng thái <strong>chờ duyệt</strong> cho tới khi quản trị bật hiển thị.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right mb15 fixed-bottom">
        <a href="{{ route('sale.post.index') }}" class="btn btn-white">Quay lại</a>
        <button class="btn btn-primary" type="submit">
            {{ cai_dat('sale_post_approval', 'on') === 'off' ? 'Đăng bài' : 'Gửi bài' }}
        </button>
    </div>
</form>
