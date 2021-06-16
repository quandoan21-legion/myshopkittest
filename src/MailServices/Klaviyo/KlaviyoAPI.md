 # KLAVIYO API

## 1.Lấy Klaviyo API key.

<<<<<<< HEAD
###Link lấy API key:
=======
### Link lấy API key:
>>>>>>> MYS-63 add post email and phpunit
https://www.klaviyo.com/account#api-keys-tab

Theo mặc định thì Klaviyo sẽ tự động tạo public API key cho bạn. Nếu bạn vừa tạo tài khoản thì bạn phải tạo ra một private API key mới.<br>
Hãy click vào ô "Create Private API key" để tạo ra một API key mới. 

## 2.Lưu Klaviyo API key.

### Method:POST.

### Example API endpoint:

https://website.com/wp-json/myshopkit/v1/me/klaviyo/setup

##### parameters

Parameters | Data Type	 | Data Default | Description
--- | --- |:---:| ---
publicApiKey | string | - |Public API key tài khoản Klaviyo của bạn
privateAPIKey | string | - |Private API key tài khoản Klaviyo của bạn

````ts
/** Object phản hồi từ seraer*/
export interface RestObjectResponse {
    data?: any;
    /** Message trả lại từ server*/
    message: string;
    /** Status code trả lại từ server*/
    status: string;
}
````

## 3.Lấy toàn bộ Klaviyo listID.

### Method: GET.

### Example API endpoint:

https://website.com/wp-json/myshopkit/v1/me/klaviyo/lists

##### description

Để API hoạt động đúng bạn cần lưu API key của bạn trước khi chạy API lấy list id.

##### parameters

Parameters | Data Type	 | Data Default | Description
:---: | :---: | :---: | :---:
- | - | - |-

````ts
/** Object phản hồi từ seraer*/
export interface RestObjectResponse {
    /** Data được trả về từ server*/
    data: Data;
    /** Message được trả về từ server */
    message: string;
    /** Status code được trả về từ server */
    status: string;
}

export interface Data {
    /** Mảng chứa thông tin danh sách của Klaviyo của bạn */
    items: Item[];
}

export interface Item {
    /** Id của danh sách*/
    list_id: string;
    /** Tên của danh sách*/
    list_name: string;
}
````

<<<<<<< HEAD
##4.Lưu email vào list. 
###Method: POST.
=======
## 4.Lưu email vào list. 
### Method: POST.
>>>>>>> MYS-63 add post email and phpunit
### Example API endpoint:

https://website.com/wp-json/myshopkit/v1/me/klaviyo/lists/members

<<<<<<< HEAD
#####description
Để API hoạt động đúng bạn cần lưu API key và list id của bạn trước khi chạy API lưu email.

#####parameters
=======
##### description
Để API hoạt động đúng bạn cần lưu API key và list id của bạn trước khi chạy API lưu email.

##### parameters
>>>>>>> MYS-63 add post email and phpunit

Parameters | Data Type	 | Data Default | Description
--- | --- | :---: | ---
email | string | - |Email bạn muốn thêm vào list Klaviyo Bạn đã chọn

````ts
/** Object phản hồi từ seraer*/
export interface RestObjectResponse {
    data?: any;
    /** Message trả lại từ server*/
    message: string;
    /** Status code trả lại từ server*/
    status: string;
}
````

[comment]: <> (
    publicApiKey: Wfamnh
    privateApiKey: pk_c96e4bb9a0255662ca64a2f70651b9bb1f
    listID1: Vtu47e
    listID2: Wy9CwF
    listID3: XfTVBH)
