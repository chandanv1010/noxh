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
                        <a href="{{ route('project.milestone.index') }}">Xem tất cả dự án</a>
                    </div>
                @endif

                <form action="{{ route('project.milestone.index') }}">
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
                                    <a href="{{ route('project.milestone.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th>Mốc tiến độ</th>
                            <th style="width:150px;">Thời điểm</th>
                            <th class="text-center" style="width:130px;">Trạng thái</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($milestones as $o)
                            <tr>
                                <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                <td>{{ $o->title }}</td>
                                <td>{{ $o->date_label ?: ($o->sort_date ? $o->sort_date : '—') }}</td>
                                <td class="text-center"><span class="label label-{{ $o->status === 'done' ? 'success' : ($o->status === 'doing' ? 'warning' : 'default') }}">{{ ['pending' => 'Chưa tới', 'doing' => 'Đang làm', 'done' => 'Hoàn thành'][$o->status] ?? $o->status }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('project.milestone.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('project.milestone.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$milestones->count())
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Bấm <strong>Thêm mới</strong> để bắt đầu.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $milestones->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
