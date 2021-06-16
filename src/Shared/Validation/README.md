# Validation

Validation là class giúp quản lý đầu ra của dữ liệu, đảm bảo dữ liệu luôn theo format của bạn đặt ra.

## Cách sử dử dụng

```php
use MyShopKit\Shared\Validation\Validation;

Validation::make($aData, $aConditionals);

```

| Param | Kiểu dữ liệu | Mô tả |
|---|---| ---- |
|$aData| array | Là dữ liệu đầu ra của bạn|
|$aConditionals | array | Là format dữ liệu đầu ra của bạn |

### Danh sách hàm Validate cho Conditional

| Tên | Chức năng | Cách sử dụng | 
| --- | --- | --- |
| string | Giá trị phải là string có thể empty| |
| stringNotEmpty | Giá trị phải là string và không được empty| |
| numeric | Giá trị phải là number| |
| email | Giá trị phải là email| |
| required | Giá trị bắt buộc| |
| notEmpty | Giá trị phải không rỗng| |
| isEmpty | Giá trị phải rỗng| |
| true | Giá trị là true| |
| false | Giá trị là false| |
| notFalse | Giá trị phải khác false (0, empty, false)| |
| null | Giá trị phải là null| |
| json | Giá trị phải là json| |
| Rule::validArrayValue | Kiểu tra mảng con | |
| Rule::inArray | Giá trị phải trong array| Rule::inArray(['x', 'y'])|
| Rule::allKeyExistsInArray | Tất cả các key phải có trong array| Như trên |
| Rule::eq | Giá trị bằng | Rule:equalTo(1) |
| Rule::same | Giá trị giống | Rule:same(1) |
| Rule::notEq | Giá trị không bằng | Rule:notEq(1) |
| Rule::notEq | Giá trị không bằng | Rule:notEq(1) |
| Rule::greaterThan | Giá trị lớn hơn | Rule:greaterThan(1) |
| Rule::greaterThanEq | Giá trị lớn hơn hoặc bằng | Rule:greaterThanEq(1) |
| Rule::lessThan | Giá trị nhỏ hơn | Rule:lessThan(1) |
| Rule::lessThanEq | Giá trị nhỏ hơn hoặc bằng| Rule:lessThanEq(1) |
| Rule::count | Đếm số lương phần tử phải bằng| Rule:lessThanEq(1) |

### Ví dụ

Dữ liệu đầu ra:

```phpt
$aData  = [
    'id' => 123,
    'username' => 'Wiloke',
    'timeline' => [
        'from' => 123,
        'to' => 456
    ],
    'email' => 'x@gmail.com',
    'status' => 'errors'
];
```

Yêu cầu: Kiểm tra các giá trị:

1. id là string và bắt buộc.
2. username là bắt buộc và là string.
3. Mảng dưới timeline phải có key from, to. Giá trị from, to dưới timeline phải là string.
4. status chỉ nhận 2 giá trị là success hoặc error.
4. email có value là email

```phpt
use MyShopKit\Shared\Validation\Rule;

Validation::make($aData, [
    'id' => [
        'required',
        'string'
    ],
    'username' => [
        'required'
    ],
    'timeline' => [
        Rule::allKeyExistsInArray(['from', 'to']),
        Rule::validArrayValue([
            'from' => ['string'],
            'to' => ['string']
        ])
    ],
    'email' => 'email',
    'status' => [
        Rule::inArray(['success', 'error'])
    ]
]);
```

### Kết quả:

```
[
    "message": "The id in 123 must be string",
    "code": 400,
    "status": "error"
]
```
