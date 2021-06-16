# Clicks API

## 1.Get Clicks

### Method:GET

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/clicks

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
thisWeek | string | số clicks của popup trong tuần này
thisMonth | string | số clicks của popup trong tháng này
lastWeek | string | số clicks của popup tuần trước
lastMonth | string | số clicks của popup Tháng Trước
today | string | số clicks của popup trong ngày
yesterday | string | số clicks của popup ngày hôm qua
custom | string | Xem Bảng Dưới

Tham Số của customer (formatDate (Y-m-d))

param | type | description
--- | --- | ---
from | string |ngày bắt đầu trong khoảng tìm kiếm
to | string |ngày kết thúc trong khoảng tìm kiếm

````ts
export interface Clicks {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
}

export interface Data {
    /** type phân biệt với các api khác */
    type: 'click'
    /** summary là tổng số lượng clicks dựa theo filter*/
    summary: number
    /** messege là tin nhắn trả lại trên sever*/
    timeline: Timeline[]
}

export interface Timeline {
    /** summary là số lượng clicks dựa theo filter (ví dụ là tổng số clicks cuả 1 tháng trong filter 4 tháng trước)*/
    summary: number
    /** from là ngày đầu tiên của filter. Tra lai timestamp*/
    from: srting
    /** to là ngày cuối của filter. Tra lai timestamp*/
    to: srting
}
````

## 2.Get Click With PopupID

### Method:GET

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/clicks/:id

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
thisWeek | string | số clicks của popup trong tuần này
thisMonth | string | số clicks của popup trong tháng này
lastWeek | string | số clicks của popup tuần trước
lastMonth | string | số clicks của popup Tháng Trước
today | string | số clicks của popup trong ngày
yesterday | string | số clicks của popup ngày hôm qua
custom | string | Xem Bảng Dưới

Tham Số của customer (formatDate (Y-m-d))

param | type | description
--- | --- | ---
from | string |ngày bắt đầu trong khoảng tìm kiếm
to | string |ngày kết thúc trong khoảng tìm kiếm

````ts
export interface Timeline {
    /** summary là số lượng clicks dựa theo filter (ví dụ là tổng số clicks cuả 1 tháng trong filter 4 tháng trước)*/
    summary: number
    /** from là ngày đầu tiên của filter. Tra lai timestamp*/
    from: srting
    /** to là ngày cuối của filter. Tra lai timestamp*/
    to: srting
}

export interface clicks {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
}

export interface Data {
    /** type phân biệt với các api khác */
    type: 'click'
    /** summary là tổng số lượng clicks dựa theo filter*/
    summary: number
    /** messege là tin nhắn trả lại trên sever*/
    timeline: Timeline[]
}
````

## 3.Update Click

### Method:PUT

### API endpoint:

https://website.com/wp-json/myshopkit/v1/insights/popups/clicks/:id

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
export interface Clicks {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái của clicks sau khi trả về*/
    status: 'success' | 'error'
}

export interface Data {
    /** id là id của clicks*/
    id: string
    /** type phân biệt với các api khác */
    type: 'click'
    /** summary là tổng số lượng clicks dựa theo filter*/
    summary: number
    /** timeline là tin nhắn trả lại trên sever*/
    timeline: []
}
````
