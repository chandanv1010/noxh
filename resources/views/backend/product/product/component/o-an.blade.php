{{--
    Cac o KHONG lien quan den du an nha o xa hoi.

    Bang `products` la bang san pham cua ma nguon goc (ban hang: gia, ton
    kho, bao hanh, xuat xu, uu dai, bien the...). NOXH.vn dung lai chinh bang
    do de luu DU AN, nen man hinh them/sua du an truoc day do ra ca mot loat
    o cua ban hang - nguoi nhap du an khong biet dien gi vao do.

    O day KHONG xoa han cac o ay ma doi thanh o an mang dung gia tri cu:

      - Mot so cot (code, price, stock, no_offer) khai NOT NULL trong CSDL.
        Khong gui len thi ban ghi moi dung vao gia tri mac dinh, con ban ghi
        cu dang sua se bi ep ve 0.
      - Du an cu da co gia tri trong nhung cot nay; bo han o di la moi lan
        quan tri bam Luu se xoa sach chung.

    Bo han cac cot nay chi nen lam cung luc voi mot migration don bang
    `products` - viec do nen tach rieng, khong lam lan trong man hinh nhap.
--}}
@php
    $an = [
        'code' => $product->code ?? time(),
        'price' => $product->price ?? 0,
        'stock' => $product->stock ?? 0,
        'made_in' => $product->made_in ?? null,
        'warranty' => $product->warranty ?? null,
        'iframe' => $product->iframe ?? null,
        'no_offer' => $product->no_offer ?? 0,
        'promotion_content' => $product->promotion_content ?? null,
    ];
@endphp

@foreach($an as $ten => $giaTri)
    <input type="hidden" name="{{ $ten }}" value="{{ old($ten, $giaTri) }}">
@endforeach
