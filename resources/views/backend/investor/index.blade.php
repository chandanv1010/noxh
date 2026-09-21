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

                <form action="{{ route('investor.index') }}">
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
                                    @include('backend.dashboard.component.filterPublish')
                                    @include('backend.dashboard.component.keyword')
                                    <a href="{{ route('investor.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th>Chủ đầu tư</th>
                            <th style="width:140px;">Tên viết tắt</th>
                            <th style="width:130px;">Hotline</th>
                            <th class="text-center" style="width:90px;">Số dự án</th>
                            <th class="text-center" style="width:100px;">Tình trạng</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($investors as $o)
                            <tr>
                                    <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                    <td>{{ $o->name }}</td>
                                    <td>{{ $o->short_name ?: '—' }}</td>
                                    <td>{{ $o->hotline ?: '—' }}</td>
                                    <td class="text-center">{{ $o->projects_count }}</td>
                                    <td class="text-center js-switch-{{ $o->id }}">
                                        <input type="checkbox" value="{{ $o->publish }}" class="js-switch status" data-field="publish" data-model="{{ $config['model'] }}" {{ ($o->publish == 2) ? 'checked' : '' }} data-modelId="{{ $o->id }}" />
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('investor.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                        <a href="{{ route('investor.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                    </td>
                            </tr>
                        @endforeach

                        @if(!$investors->count())
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Bấm <strong>Thêm mới</strong> để bắt đầu.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $investors->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
