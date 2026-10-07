<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>
            <input type="checkbox" value="" id="checkAll" class="input-checkbox">
        </th>
        <th>Tên Nhóm Thành Viên</th>
        <th class="text-center">Số thành viên</th>
        <th>Mô tả</th>
        {{-- Cột này trước đây KHÔNG có, nên nhìn danh sách không biết nhóm nào
             đang là nhóm nhân viên kinh doanh - phải mở từng nhóm ra xem. --}}
        <th class="text-center" title="Nhóm được bật cờ này thì thành viên đăng nhập ở /sale, và xuất hiện ở ô chọn nhân viên phụ trách trong form dự án.">
            Nhân viên kinh doanh
        </th>
        <th class="text-center">Tình Trạng</th>
        <th class="text-center">Thao tác</th>
    </tr>
    </thead>
    <tbody>
        @if(isset($userCatalogues) && is_object($userCatalogues))
            @foreach($userCatalogues as $userCatalogue)
            <tr >
                <td>
                    <input type="checkbox" value="{{ $userCatalogue->id }}" class="input-checkbox checkBoxItem">
                </td>
                <td>
                    {{ $userCatalogue->name }}
                </td>
                <td class="text-center">
                    {{ $userCatalogue->users_count }} người
                </td>
                <td>
                    {{ $userCatalogue->description }}
                </td>
                <td class="text-center">
                    <input type="checkbox" class="doi-co-sale" data-id="{{ $userCatalogue->id }}"
                           {{ ($userCatalogue->is_sale == 1) ? 'checked' : '' }}
                           aria-label="Nhóm {{ $userCatalogue->name }} là nhóm nhân viên kinh doanh">
                </td>
                <td class="text-center js-switch-{{ $userCatalogue->id }}"> 
                    <input type="checkbox" value="{{ $userCatalogue->publish }}" class="js-switch status " data-field="publish" data-model="{{ $config['model'] }}" {{ ($userCatalogue->publish == 2) ? 'checked' : '' }} data-modelId="{{ $userCatalogue->id }}" />
                </td>
                <td class="text-center"> 
                    <a href="{{ route('user.catalogue.edit', $userCatalogue->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                    <a href="{{ route('user.catalogue.delete', $userCatalogue->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
{{  $userCatalogues->links('pagination::bootstrap-4') }}
