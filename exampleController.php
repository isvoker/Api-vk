<?php
        
            $ProductsOptions = array(
                'category_id' => Request::getVar('category', 'numeric'),
                'limit' => 1,
            );

            $Products = ProductsClass::getProducts($ProductsOptions);

            function items($id, $access_token) {

                $item = ProductsClass::getProduct( $id );

                if ($item['isLoad'] == 1){
                    return;
                }
                $price = $item['product_price'];

                if (!$price) {
                    return;
                }

                $vk = new VKApiClient('5.131');

                $upload_url = $vk->photos()->getMarketUploadServer($access_token, array(
                    'group_id' => '...',
                    'main_photo' => 1
                ));

                $image_path = "...";
                $size = getimagesize($image_path);

                if($size[0]<400 || $size[1]<400) {
                    $image_path = '...';
                }

                $curl = curl_init($upload_url['upload_url']);
                curl_setopt_array($curl, array(
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POSTFIELDS => array(
                        'file1' => curl_file_create($image_path),
                    ),
                ));
                $img_attach = json_decode(curl_exec($curl), true);

                if (!$img_attach['photo']) {
                    return;
                }
                curl_close($curl);

                $savePhoto = $vk->photos()->saveMarketPhoto($access_token, array(
                    'group_id' => '...',
                    'photo' => stripslashes($img_attach['photo']),
                    'server' => $img_attach['server'],
                    'hash' => $img_attach['hash'],
                    'crop_data' => $img_attach['crop_data'],
                    'crop_hash' => $img_attach['crop_hash'],
                ));

                $label = [];

                if ($item['product_seo_title']) {
                    $label[] .= "{$item['...']}\n";
                    $label[] .= "\n";
                }
                if ($item['product_attributes']) {
                    foreach ($item['...'] as $key => $value) {
                        $label[] .= "{$value['...']} : {$value['...']} \n";
                    }
                    $label[] .= "\n";
                }

                $label[] .= "...";

                $string = implode('', $label);

                $product = $vk->Market()->add($access_token, array(
                    'owner_id' => '...',
                    'name' => $item['...'],
                    'price' => $price,
                    'description' => $string,
                    'category_id' => '...',
                    'main_photo_id' => $savePhoto[0]['id'],
                    'sku' => $item['id'],
                ));

                $vk->Market()->addToAlbum($access_token, array(
                    'owner_id' => '...',
                    'item_ids' => $product,
                    'album_ids' => Request::getVar('albumId', 'numeric')
                ));

                DBCommand::doUpdate(
                    '...',
                    ['isLoad' => 1],
                    DBCommand::qC('id') . ' = ' . DBCommand::qV($id)
                );
            }

            foreach ($Products as $test) {
                $id = $test['id'];
                items($id,$access_token);
                sleep(0.4);
            }
            break;
