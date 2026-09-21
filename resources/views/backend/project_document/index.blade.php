@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])

<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="uk-flex uk-flex-middle uk-flex-space-between">
                    <h5>{{ $config['seo']['index']['table'] }}</h5>
                    @include('backend.dashboard.component.toolbox', ['model' => $config['model']])
                </div>
            </div>
            <div class="ibox-content">

                @if(request('product_id'))
                    <div class="alert alert-info">
                        Đang xem của một dự án cụ thể.
                        <a href="{{ route('project.document.index') }}">Xem tất cả dự án</a>
                    </div>
                @endif

                <form action="{{ route('project.document.index') }}">
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
                                    @include('backend.dashboard.component.filterPublish')
                                    @include('backend.dashboard.component.keyword')
                                    <a href="{{ route('project.document.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th>Tên hồ sơ</th>
                            <th style="width:170px;">Số hiệu</th>
                            <th class="text-center" style="width:130px;">Ngày ban hành</th>
                            <th class="text-center" style="width:80px;">File</th>
                            <th class="text-center" style="width:100px;">Tình trạng</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $o)
                            <tr>
                                <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                <td>{{ $o->title }}</td>
                                <td>{{ $o->doc_number ?: '—' }}</td>
                                <td class="text-center">{{ $o->issued_date ? \Illuminate\Support\Carbon::parse($o->issued_date)->format('d/m/Y') : '—' }}</td>
                                <td class="text-center">@if($o->file)<i class="fa fa-check text-success"></i>@else—@endif</td>
                                <td class="text-center js-switch-{{ $o->id }}">
                                    <input type="checkbox" value="{{ $o->publish }}" class="js-switch status" data-field="publish" data-model="{{ $config['model'] }}" {{ ($o->publish == 2) ? 'checked' : '' }} data-modelId="{{ $o->id }}" />
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('project.document.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('project.document.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$documents->count())
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Bấm <strong>Thêm mới</strong> để bắt đầu.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $documents->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
