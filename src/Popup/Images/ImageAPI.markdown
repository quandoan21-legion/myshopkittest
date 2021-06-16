# UploadAPI

## 1.Upload IMG
###API endpoin:
####method:POST
https://website.com/wp-json/myshopkit/v1/images
###Request
#####headers
1.authentication: bearer token (string)
#####parameters
1.content (string):

dạng base64: thì là chuỗi base64

dạng self_hosted: url ảnh

dạng mặc định: upload qua form

2.source (string) :

có 3 dạng:

1. base64

2.self_hosted

3. không nhập thì mặc định bằng $_File
````ts
export interface UploadImage {
    item: Item
    /** msg la tin nhan code tra lai*/
    msg: string
    /** status la trang thai cua api sau xu ly*/
    status: 'error' | 'success'
}

export interface Item {
    /** id la id cua hinh anh*/
    id: number
    /** url la duong dan cua hinh anh*/
    url: string
    /** msg la tin nhan code tra lai*/
    msg: string
    /** status la trang thai cua api sau xu ly*/
    status: 'error' | 'success'
}

````
## 2.GetImages
###API endpoin:
####method:GET
https://website.com/wp-json/myshopkit/v1/images


#####parameters
1.post_mime_type (string):Kiểu type ảnh mặc định là image/jpeg,image/jpg,image/png\
2.order (string) : sắp xếp theo cái j,mặc định là id
3.posts_per_page (number) : số ảnh trả về, mặc định 20
4.orderby (string) : sắp xếp theo chiều nào, mặc định ASC
````ts
export interface GetImages {
    items: Item[][]
    /** msg la tin nhan code tra lai*/
    msg: string
    /** status la trang thai cua api sau xu ly*/
    status: 'error' | 'success'
    /** paged so trang */
    paged: number
}

export interface Item {
    /** id la id cua hinh anh*/
    id: number
    /** title la ten cua hinh anh*/
    title: string
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
    thumbnails: Thumbnail[]
}

export interface Thumbnail {
    /** id la id cua hinh anh*/
    id: number
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
}
````
## 3.GetImage
###API endpoin:
####method:GET
https://website.com/wp-json/myshopkit/v1/images/id
###Request
#####headers
1 id của ảnh
````ts
export interface GetImage {
    items: Item[]
    /** msg la tin nhan code tra lai*/
    msg: string
    /** status la trang thai cua api sau xu ly*/
    status: 'error' | 'success'
}

export interface Item {
    /** id la id cua hinh anh*/
    id: number
    /** title la ten cua hinh anh*/
    title: string
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
    thumbnails: Thumbnail[]
}

export interface Thumbnail {
    /** id la id cua hinh anh*/
    id: number
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
}
````
## 4.Get Me Images
###API endpoin:
####method:GET
https://website.com/wp-json/myshopkit/v1/me/images

###Request
#####headers
1.authentication: bearer token (string)
#####parameters
1.post_mime_type (string):Kiểu type ảnh mặc định là image/jpeg,image/jpg,image/png\
2.order (string) : sắp xếp theo cái j,mặc định là id\
3.posts_per_page (number) : số ảnh trả về, mặc định 20\
4.orderby (string) : sắp xếp theo chiều nào, mặc định ASC
````ts
export interface GetImages {
    items: Item[][]
    /** msg la tin nhan code tra lai*/
    msg: string
    /** status la trang thai cua api sau xu ly*/
    status: 'error' | 'success'
    /** paged so trang */
    paged: number
}

export interface Item {
    /** id la id cua hinh anh*/
    id: number
    /** title la ten cua hinh anh*/
    title: string
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
    thumbnails: Thumbnail[]
}

export interface Thumbnail {
    /** id la id cua hinh anh*/
    id: number
    /** url la duong dan cua hinh anh*/
    url: string
    /** width la rong cua hinh anh*/
    width: number
    /** height la chieu cao cua hinh anh*/
    height: number
}
````