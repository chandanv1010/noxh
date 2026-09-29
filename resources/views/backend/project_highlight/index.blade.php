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
                        <a href="{{ route('project.highlight.index') }}">Xem tất cả dự án</a>
                    </div>
                @endif

                <form action="{{ route('project.highlight.index') }}">
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
                                    <select name="group" class="form-control mr10" style="width:auto;">
                                        <option value="">[Cả hai khối]</option>
                                        @foreach(\App\Models\ProjectHighlight::NHOM as $ma => $ten)
                                            <option value="{{ $ma }}" {{ request('group') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    @include('backend.dashboard.component.keyword')
                                    <a href="{{ route('project.highlight.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th style="width:230px;">Thuộc khối</th>
                            <th>Dòng trên</th>
                            <th>Dòng dưới</th>
                            <th style="width:200px;">Hình minh họa</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($highlights as $o)
                            <tr>
                                <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                <td>{{ \App\Models\ProjectHighlight::NHOM[$o->group] ?? $o->group }}</td>
                                <td>
                                    {{ $o->title }}
                                    <div class="text-muted" style="font-size:12px;">{{ optional($o->project)->code }}</div>
                                </td>
                                <td>{{ $o->subtitle ?: '—' }}</td>
                                <td>{{ \App\Classes\NoxhIcon::DANH_SACH[$o->icon] ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('project.highlight.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('project.highlight.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$highlights->count())
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Các dự án đang dùng bốn ô mặc định trong Cấu hình chung.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $highlights->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
