@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])

<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="uk-flex uk-flex-middle uk-flex-space-between">
                    <h5>{{ $config['seo']['index']['table'] }}</h5>
                </div>
            </div>
            <div class="ibox-content">

                @if(request('product_id'))
                    <div class="alert alert-info">
                        Đang xem của một dự án cụ thể.
                        <a href="{{ route('project.unit.index') }}">Xem tất cả dự án</a>
                    </div>
                @endif

                <form action="{{ route('project.unit.index') }}">
                    <div class="filter-wrapper">
                        <div class="uk-flex uk-flex-middle uk-flex-space-between">
                            <div class="perpage">
                                @php $perpage = request('perpage') ?: old('perpage'); @endphp
                                <select name="perpage" class="form-control input-sm perpage filter mr10">
                                    @for($i = 20; $i <= 200; $i += 20)
                                        <option {{ ($perpage == $i) ? 'selected' : '' }} value="{{ $i }}">{{ $i }} bản ghi</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="action">
                                <div class="uk-flex uk-flex-middle">
                                    <select name="product_id" class="form-control setupSelect2 mr10">
                                        <option value="">[Tất cả dự án]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ request('product_id') == $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                        @endforeach
                                    </select>
                                    @include('backend.dashboard.component.keyword')
                                    <a href="{{ route('project.unit.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th style="width:90px;">Ảnh</th>
                            <th>Loại căn hộ</th>
                            <th style="width:160px;">Diện tích</th>
                            <th style="width:180px;">Giá dự kiến</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($units as $o)
                            <tr>
                                <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                <td>
                                    @if($o->image)
                                        <img src="{{ $o->image }}" alt="" style="width:70px;height:50px;object-fit:cover;">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $o->name }}
                                    @if($o->publish != 2)<span class="label label-default ml5">Đang ẩn</span>@endif
                                    <div class="text-muted" style="font-size:12px;">{{ optional($o->project)->code }}</div>
                                </td>
                                <td>{{ khoang_so($o->area_from, $o->area_to, ' m²') ?: '—' }}</td>
                                <td>{{ khoang_so($o->price_from, $o->price_to, ' ' . $o->price_unit) ?: '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('project.unit.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('project.unit.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$units->count())
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Bấm <strong>Thêm mới</strong> để bắt đầu.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $units->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
