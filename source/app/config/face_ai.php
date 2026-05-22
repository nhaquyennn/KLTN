<?php

return [
    /*
     * URL ngrok cua PHP web app chay qua XAMPP port 80.
     * Vi du: https://your-php-web.ngrok-free.app
     */
    'php_web_base_url' => '',

    /*
     * FastAPI AI server chay local tren may chu PHP.
     * Mobile frontend khong goi truc tiep URL nay.
     */
    'fastapi_internal_url' => 'http://127.0.0.1:8000',
    'FACE_API_URL' => 'http://127.0.0.1:8000',
    'FACE_API_TIMEOUT' => 30,
];
