# Views API

## 1.Get Views

### Method:GET

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/views

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>?filter</th>
<th>string</th>
<th>nếu không bắn lên hoặc rỗng thì lấy today</th>
<th>Xem Bên Dưới</th>
</tr>
</table>
Tham Số của filter

param | type | description
--- | --- | ---
thisWeek | string | số views của popup trong tuần này
thisMonth | string | số views của popup trong tháng này
lastWeek | string | số views của popup tuần trước
lastMonth | string | số views của popup Tháng Trước
today | string | số views của popup trong ngày
yesterday | string | số views của popup ngày hôm qua
custom | string | Xem Bảng Dưới

Tham Số của customer (formatDate (Y-m-d))

param | type | description
--- | --- | ---
from | string |ngày bắt đầu trong khoảng tìm kiếm
to | string |ngày kết thúc trong khoảng tìm kiếm

````ts
export interface Views {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
}

export interface Data {
    /** type phân biệt với các api khác */
    type: 'view'
    /** summary là tổng số lượng views dựa theo filter*/
    summary: number
    /** messege là tin nhắn trả lại trên sever*/
    timeline: Timeline[]
}

export interface Timeline {
    /** summary là số lượng views dựa theo filter (ví dụ là tổng số clicks cuả 1 tháng trong filter 4 tháng trước)*/
    summary: number
    /** from là ngày đầu tiên của filter. Tra lai timestamp*/
    from: srting
    /** to là ngày cuối của filter. Tra lai timestamp*/
    to: srting
}
````

## 2.Get Views With PopupID

### Method:GET

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/views/:id

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>?filter</th>
<th>string</th>
<th>nếu không bắn lên hoặc rỗng thì lấy today</th>
<th>Xem Bên Dưới</th>
</tr>
</table>
Tham Số của filter

param | type | description
--- | --- | ---
thisWeek | string | số views của popup trong tuần này
thisMonth | string | số views của popup trong tháng này
lastWeek | string | số views của popup tuần trước
lastMonth | string | số views của popup Tháng Trước
today | string | số views của popup trong ngày
yesterday | string | số views của popup ngày hôm qua
custom | string | Xem Bảng Dưới

Tham Số của customer (formatDate (Y-m-d))

param | type | description
--- | --- | ---
from | string |ngày bắt đầu trong khoảng tìm kiếm
to | string |ngày kết thúc trong khoảng tìm kiếm

````ts
export interface Timeline {
    /** summary là số lượng views dựa theo filter (ví dụ là tổng số clicks cuả 1 tháng trong filter 4 tháng trước)*/
    summary: number
    /** from là ngày đầu tiên của filter. Tra lai timestamp*/
    from: srting
    /** to là ngày cuối của filter. Tra lai timestamp*/
    to: srting
}

export interface View {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
}

export interface Data {
    /** type phân biệt với các api khác */
    type: 'view'
    /** summary là tổng số lượng views dựa theo filter*/
    summary: number
    /** messege là tin nhắn trả lại trên sever*/
    timeline: Timeline[]
}
````

## 3.Update View

### Method:PUT

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/views/:id

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
</table>

````ts
export interface View {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái của views sau khi trả về*/
    status: 'success' | 'error'
}

export interface Data {
    /** id là id của views*/
    id: string
    /** type phân biệt với các api khác */
    type: 'view'
    /** summary là tổng số lượng clicks dựa theo filter*/
    summary: number
    /** timeline là tin nhắn trả lại trên sever*/
    timeline: []
}
````
