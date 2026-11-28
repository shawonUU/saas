<?php
/**
 * SaaS global inline searchable dropdown (native-select adapter) — 2026-11-28.
 * One editable select field; no separate search input. Supports scrollable modals.
 * php saas_inline_search_dropdown_patch.php --dry-run
 * php saas_inline_search_dropdown_patch.php --apply
 * php saas_inline_search_dropdown_patch.php --rollback=BACKUP_ID
 */
declare(strict_types=1);
$root = __DIR__;
$mode = $argv[1] ?? '--help';
$targets = [
    'resources/views/portals/partials/theme-styles.blade.php' => ['css'],
    'resources/views/portals/partials/ui-controls.blade.php' => ['js'],
    'resources/views/public/business/public/ecommerce/layout.blade.php' => ['css','js'],
];
$assets = [
    'public/assets/saas-inline-search-select.js' => gzuncompress(base64_decode(
        'eNq1O01vJMd1d/2K2j24u73DWVKCBIEUl1jtSpZs7ZIRV9KB2UPNdA2nzZ6uVncPP7ziIYfEByPIwXAOgQFfDK4QBIiRQ47JXyHyS/I+qqqrun'
        .'s+tIYBWztd9erVe6/edxUf/1ycSnkqsiLPCiVqJavpXE5yJQrZZJdqp1a5mjZCprJsVCV+EO/vvv/Rzt7ezvsfj98TPxfHsEqlWUNrpnox0RN9'
        .'DejKZTMSL48B41QXqaxuDG4xy1SejsWruRK6ys6zQubiE97lCSKs1EJmRS0aAKj1spoC3Ew01bKBtboSlzLPUqBNFyP8Xoh6OVlkdQ0DQhapUN'
        .'dZ3WTFufjlKRL4+L04TsThE/HmPSGiZQ1ImyqbNtEBfGczEV9lRaqvxi/VtTwlCk+JlqMxi+RbVSHqBOhqllWBq4ChugE0slG1OBSFuhLfKXnx'
        .'QpZx0s7nID0ze6qaYEYCdYciTvV0uVBFM7Y/PssVfRPADz+ISBVRMm70V/pKVc9kreJkDNtWTf1d1szjaALT4kjgv2KfoNtNrnSVInW82eEhQx'
        .'2RGIQ5C1hzf/cv93f/en/3P/d3P96//e393W/F/d1/3d/9L46+xQ/45x/u7+B/f6QfP0YjoRZlc0OL/3D/9ne08ncA+Y/3d/9+f/ff8C3oF+D8'
        .'/f3bP8EP+P5P/MaPHwE54FjoShGKf7q/+wsAdtffETVA2Z+ZjIDMzTQSl9O51jXv8iOB/+X+7T/f3/0H4fk33ucPNGoY0yXqVU0rhqlBAiJAfg'
        .'sC74jy1U2pxFxVSjTajHqyeqnFQjbTOWqm2aaVAi2FVYWsKn1Fql+pepk3dZeTU2OOhUESEH06h8UWORLZqsNCXn+b1Rka6aHY+2gXZ3IFWqy+'
        .'X6piiqNuTE4bo7vLPLeDpSpSoP1E1xnih9mZzGvV7jDNlcRhsM+lQoM7BTsrzmP+PgI1BV0uwGDBfn+j4ujl5796ZrR7KnPl6zgsXKDBAO7Zsp'
        .'jSfuyOXqnrJmZnkRj58+7ANezNM2P+R6XHLIqz3dcHBIrm/gAhwbbgn7EhFY0jsgYOPxnYfCJcA7s+00WDtsm0jStV5nKq4sd/Xz96fD4SkYjI'
        .'wm/7NJ8g4Fznqao6pJstDNXnqnnaAPrJsgHxgIuTO2W7FAgEqg0onFl1w6qgqzjiIz8jdg4fPnwdJUcDNON68gpj1qYeuVn9TY1ufB2V4NnBA4'
        .'HCgE/+4tWLr5gI47jEz34mHhgSF6C9WQnqBmP2WODcxSegfgEcGoWq4+iMOA6izusRD7IxmcHDh3o2Qx579HO0MIcek3/u8PC0quTNeFbpBU8b'
        .'TRkbm0mAmjKO+WsErKbqmmJHzFgED1mTG7Gu75tPVqcReNyJyt3glrozMhukGR1B6tbbATy9T7VGIzMEjqc5nGLd0PmfV3pZnllgkI7DOM/SVB'
        .'UOH39uhY1BfVw07lCVsmqDFmicPH8pF8aejk9e/eLr429OMOQMgY9JShi1IsJ9myTjWZZDihFnjVqg0B/gD0Nv/7CJ3NieL1k2+y0/UgexGvwD'
        .'Qxz0PRxjYJ0A8patWu6XutSXqtrRJUbjRHhAQJs64WmO78Kfneayrr+CZAQOfAEwcVRLWe9QjrSDEDvgQfOJnF5E/cXmmA4x8VH+LGVWoLe+r5'
        .'BVJnfUdQnpj0ohIkTkmkOsvI4p6S5lcaSqnoKTl0UTrpxn5/Mc/o8OdmevKy1jQVkNSl6Q1wUR3RRTY399I3VzI1FCkFPVpfo7dGc2ogRHunoP'
        .'/5B9Dp29HIpgsR0fksn3y6yiFRzSxOPH4iX5Iev0TM6p6gORNTV4ARoQcxB4DknxDPI3sB2K25cmyjLu3maeR0f16wcIj+Zkq3O35MO5m4gb8G'
        .'2nk8SLgYHgk2AHExODeLuKprkG5W7VvNHn53mo5lbqQNzgaSQr1MmdIkQKa6yH5kQTa/vt2j4IuBonztEQg4erWcSUGhPq3qq+NpcmHXKOaE2a'
        .'tMIZdZQdPfMDf+dA69843q0BED0WdYXKehgQDlnFp3pJRD3LM/C8XwOMlR2vusygRjkUphACBV7K/FsYK3XVBHCvdIkJHkwdjSEIgzLiCBC8G4'
        .'B9oYy3YMg5f2L2wTtkwE7FQMG6T3XT6AWuQ7SPLKIA5rsshTrQor6irw5mAmnFijIZTxj1J4waFtBoA7+f2H03CrcuwUw/VblGYb2QzRyCxHW8'
        .'O3KE7wh/rx2xtxuImdY/nWgKPP56R8sOk9dbKc0ij4JPIIn/kHKrFusTD8BfDtu4I3H7fgwb80dWxB/swlfM2xz5KPc9lAkQ9kESEHZlTsMhcu'
        .'iJJ5oeib0PP0xG7c7wObIHCax+FKLM1Syk0yOTkCKAt/7K/PtxiAY83ExV4Pc6nAMWL8rW00rn+RdOQfdQKk5cAcKGdN+KyJFnVPWj4BS7m++I'
        .'DxMQpaOgVZgP7PEbpXkEkLzr8eTXOA6+NTsPaW5ucnBpNiNFceyz1B6JqLwGTwtE7BO9doBktG9EZQcdm/uegvCkycjWZDQDrp5EA3jp376nhJ'
        .'JlqU667pKjUcdnOp+fOC77brXNizC7B+x187TIFtQY+ryCPDS2vtnwcdulyOU1NkqY5J6VwmwRVplTCPicCDgva+K958ZbINDV4ryZo2cZTKUG'
        .'3UwB8ajjIJziGAp7O6AdJSuTNsTYO8ughHya51CAAdtQPnK2jgXWeKarz+R0HkOibhponigg+FOC+nK5mECkVfkYazWICeMKfQUFWLexEDDfV5'
        .'+sNrkn6A0jTCw8itIMbcqAeunrCHfLbHJxG9jxdFlhGeLOry8MW4oCG4cPIzAJOhGwDFt0mnyF1QSU1aBM7A/jVr4sGo1hNH4jJrmeXkCtU0Ah'
        .'C4oaGZoCdQwzlu9NSoyNCV8DLxSOUq8lJpiAOayYSDUHKuFeeYWowOfRgMmKHlArBHmKeQuao1ItAflP8yVIOYaF1EzwQKgw7IAkoUZeuvYTUT'
        .'lc7bm8cFznGZTI7I5N56rvkEwl/Wye5SnIL95Yt1Anq1KXmV7Wv0CaezXgA6bOWW6g9NTMgyWuczutFGxlato4SrPLyGkwAbPSc10sfHdJs9GB'
        .'gfK6BJiJUZeGZiwyj2lZojeMadqqOCh8rRyx2CaPW43A/jlzNSTUlkcWQHueqAfeFypHIDt/pZXQXEl01NvKiBoUvGSlpGh3kJSFC2XVEujj7I'
        .'vLrPZ2vnW/uhoxhNMk2JT8bckaAK9kynRvQ+AsDd0SfIPbMbA76IqIMooC4crQK6IjxzaA2SRZB0se1DZM2xqy3aitqsad9uqX1B/rYLdBwKzt'
        .'41sBz5INKljjMcBTFLxXfI2e4tqjy0O7mUuvGPWpcj4n6Z4296hWnzfkxihdhhvSS5oI6TLqyD71oG95W8l8wPCmczW92Eyrvw5XrFRQmgWrY6'
        .'iQt+j//vh7NzUgaG5eoQZiChXu6smA1g8aZN98MaewkLfvhTJzWdAT3695MjJlkK7U9saL0BudNwEN+W6cOFjDDs53+AlKTXPa3SzTMwUbN7dX'
        .'Gi91seifHIrdpJ8I2/mR6cjxyk4G30tgsEG6On1Z3eJpGx6b+nxeq+fBim6Q6+zSJI+17byFTmUu0kzm+rwGZblQQi+bOkuVeH78AgoAVTVjca'
        .'KrBsCygiawqccrRhYbDBUCr5JNbxiLrZ1c3sCvWtv6AU6ykYixxny90HhDlpX2Xm/s16q039daN92mpe3JP2QCzlDErx9S8uXUeKLTm8EWdtBv'
        .'5+jtNkq831Yn26Vr2tFeN2sgYfV0rLkp8Y7IL1zn+sp0y/mOwGpO1NpqA1rzZsUi7MtY059iez6EbKsLmaZre+3W4DhpeicUP7Uh7/vBcrUJwW'
        .'aLLKxHB685B+6uzgh6xUWnb2v4vf7maOACZTqXxXm/qR44GFIxLzdZAxnABfb7rq1oXkGNeKi3MKEDpT8lS9ynQt6VgVxYEjut3nU9U4n69Rni'
        .'iPEBB/+KaBM4TijolhOQVu1QJwc/CRHvvw5Tr1uhijleu3buZrli6dzb9p0mv1UZz2XdwrzxrmJqbBPbqZFxokeGDY4r/BMqAud4GMr6Fxd8Al'
        .'x8Lisaqnh9sGXG0t409KNxFFTAuN8apHyEVhVIadBTIUaM45E/4e8XO49sx45Eb+gRXuXSnWaCvz3/wdv6yOWy0TBXQkVKu+vZDBIK40xKlec2'
        .'ofM87pCrsbm+fXoV8jbULfE2xoU5+LuNi0BzINZBGWYWbLPRygtJc6+RumPkl1ZGVFjmxI8e2Ycpm3ahRDoaDT+i8CD85xM0Uh+Nz3Zfr3ooYS'
        .'ALPFb3boLfIayhqMkaOo0BcJODQhzZLgdtA9JA/omTkQcXUmGyEpT7QhZLmQc4+3I3EYQkb8W+ErnVt44WrLq0XnlumB4BLnoExWQlLlvDt4FF'
        .'cAG7WNLtFr4M5IeB+OavgXjy2F7NphDNSYojgbmWNA8P+Ykgvzk0z7Vaox0M9byxi/EM3siJjVy2lWST1aKGpPFp+ms5bbMt4HHWKEyQgD30Xb'
        .'4bM+mW8Yw0HiRzrkZoM7Hw4vCN2ZtRj+xjS4Qf2fvnfXH2etRm9vtA9kjoCd36VvvU8+LnYcJ67dp5bXNfioUtSIIE1F7mi+7tvpMEwFF4Q6kq'
        .'yHu9MMcvMP2rfy4sNiGwAffd15N6YEP4koJU28WmgbFJFJ6rmYSsHDNMP5GAz7aocXMDytzuE2R6YefZdhP6TqgN1NwWaC93qLEwINg8gzR0gC'
        .'uTETBzjazAHdpk72gcR2MvaiZhF2E7efRq9VsvX3GhrUcvLXfn6JKuriKx8d+U2EA0rV1+VOHe9LLs6LHaYqGgKmpUfoMXXFD9F7r/2Bg81Ng9'
        .'3eLDy737bxGcb3Bmq1ixog9ZGa5Me8hNz2LLdwjJIM2bCAxt5k3A/fDzH6u9AZHruDEux3sUwTc8teoUhX2wTdRfqJtUXxVDuo1UsZbSJQhWkE'
        .'/xuexzhMcgPTT5TRn5er5Ky3m8hjr+pNKlPJdtgfYTD1gMtFM61xGP1vFxJPYEeuu2pDB16gD7n0HWUkXdBn6fTL8c+Kuk4N0pdDpTZx0mX9PV'
        .'Uti8Ml06JH0P0+dg/e5rwWFpoCmadAvjfjN5jZDqqSxVNPyS6N01Iyxb1xHwSk6iTpdqkxVM8mXVM2HwjSc6wyP33CC9O+tSjU/JL5Qq+WGa0J'
        .'wz2QrBOkTq573IoEZuZH1h/0pi8D3VmrqPi35MZdrXM7cdNrnz2eOyZG4227sBpFfyJNKFBr7pSIci3cBVdrLqfLehc2W09e+EtiTEd7EPcCW+'
        .'RdJXQ3WLu67gy3STWXSMOTQMcxffu4dJOoyy6dl00PyVyotlQ8p9bIbjlSHur3hnt80LOxdMegnCMP32h0tf32B90FRKcVcF3xZkeYoH2g7ICp'
        .'hR1XMQkx2U9gBMN8a+enbjn9OVNaTWkXeVFHmXZ1F782Hr0oj4il63tHP6h3XJ0YCy4ctML+VVzatsofSyibs5cDISu8nQM4IZoJiDDmi/D+I3'
        .'jExrliFQs2q6DUPdChd13kXgrP+KlL57vSYctc+nMedkfskETQvLw0PA3aco/qr2DQovTsKNyP20PHoX3+y3gM08eEjkFnYvS9gQgRrTt0faO9'
        .'mYX3/0LGFF266+Aj/snjttR9/qyxCkLSvah0/DNtmJTB1jAdWdMkZ01d5DXvNK3Q2aFxZ2kGrBVGHbKB5i2bFVoQr5vsOqpDsnvyra5IQWZqAe'
        .'eoJkm9LBH9nZdx5Mehq20IIzsLjxGNw+3XTKTpg+IdpL6yiioatYqvLaZRQS/CtQCmz5sI0YnqjoDi6I7TKioNOgxdw39xU3nAe0A3d3QdriM+'
        .'EJqNCp8oWDZKn0JYzWIeNMHYKP8T8UqdE09/o62wHfKAWESsLFtHArr1Hz0zUfHwwlAb7bLXk3+mS47/PLeS2+nVrFGB5Un/aA9cRTW/+Jo1dx'
        .'9/XYqAEQ6y422mve0IN0rhF8R2I2TqzPGnAy+Hdb/LN9Xd6+Kq/jxL2bGkx3ewF71V/AYgjvxexOTH/HaG0idBuP27z2XRLUvmD4qsLYYuynhY'
        .'kPxv3dQbCu9Eb8EnUjuZTtZ1uQ6u9GZuqf6TsSuZk8vhePRqEzCpgzz/gHs6LsN6q7NlwV/uHC0d8MyzAfnInhf1b8QTk1boO/Kd8X0Qfj3fFu'
        .'NLIhcsRCHVFjYd/atTvHsA3cv207aNOH9s3xuoYmxW6f6FMp6y+JRkf0Cnbsn8+7A6+UTG9OmTYMkrmmB3fg9dboxPPjF+bu5SsAt38nVJED0O'
        .'Cngrtbe11fUaC/TfC//w+7fVjY'
    )),

    'public/assets/saas-inline-search-select.css' => gzuncompress(base64_decode(
        'eNq9V9uO2zYQfd+vYHcRYB2YjmXLl5WQokBQ9KF9y2OQB4oaWawlUSUpX2Lsv2dIydbF6+z2giLAxtTwMjxz5szww3vyWyYjlhFRZKIAqoEpnh'
        .'Iu80hG8hAQkwLZCS2iDEgiIIuJMBqyhDDOoTTaTfirAnWckPcf7tAE3Ew0Y5q6TWjBjNjBKRa6zNgxKGQBP4m8lMqwwjzfdaaeSqmFEbIIFGRu'
        .'VXhe1XiXZHAIWSY2BRUGch1oo8DwNLSGwCMeYZWR4V7EJg286fRdmLMD7Q5F0Qyn4Q6UEZxl1G0Y5CKOMwi5zKTC81JQou9eELDEgDpxWRgoTH'
        .'B/H14cZpGWWWUgVGKTmsBblIfQyDLA7fnjYvqOUDIvD6PGsxVaU3Az7U+jWKETqfJAScMMPPqLGDajMJIqBkWbLSfL8kDwFBETXimFHnyyrp5n'
        .'RdIYmd+eJkvGhTkGk8UC3RZ4BUVhh2btQtK7KenGTxRlZS7hizLJt2388PQD1eKbKDbBxZFDx94BvPO1jUh3ai9UHUMvuL0FaiOK3sYli2P0pc'
        .'Fsjph3jKwskd2s4DBgYUj3EG2FobdnRIxvN0pWRUxFzjZXdsRaI28MHEznqx1SiURLMrkPIMtEqYUOE2TQixS7Bj5IJK/0SVbGJkAwa4Nro0pz'
        .'cXgUBdFqE413TD1SWir0Tx3HD6vF0odoRJB9Y8ewklk2jMJmLyqTRIPBRHAxTFmMLk6J/YewvX1/b7B/70Ld+yCFGKpIfLpQcXmGrZCIfYYYQf'
        .'zq8nMaXnaZL17B8AtTguHPHSZ6/PHeqAruv54autb5/gDTBVsmN4SJ5lBUrTol4gBxJ8qisDgOyPlaYnxDh2Lk9czzV/56vvRXHWMv237A82DR'
        .'I/iZafQ49MZaNFcyy2gEKdsJvLPVMSaKRkAC70KtOtCVoLWlCfxeU0ubZtSYHuZPPqrVaDTqXb3WLRaLSgee1/OxTaTgck7JCsjaY7pDXamE8f'
        .'Op+xRlf/yQJMmof2QdxcuGNu/a/TqjmKnt+MFLZk/z1dDpThJ4awTDR3AJEp89rsbefDxfjCezdXeJTWMbYwg8zJiwxjdiqlExkyK4DcgBRhPz'
        .'jmhzxBrTKp2rDyh3uz1WCA+ze3RNvC8pViYovr6lhLoFNp0eg1KWNupUllCM3KfhPJow6y/fjt60tSwt+y9TXS3+s9JGJEd6rokoAhyQYmYPUP'
        .'QqNQdbcsINK1HeEa2G1JZ10wudn3Bk+RI6gWpKpDfxB5SyRbNRjqaUhXucQCMFbBu4v9R+CB1fqHMKr6ZyloWVxn3qLuW68DV3nAhNGbcNyPja'
        .'GKQW1lOHx69Lpb+cQYJS6fel8gaB23XL5QrY6CUXa02r7wGtqDlK7pvWYjq9vfKsppeVFzn1F6+pMrX3Lk/noNlwuqgR2/V0ksI7DxuH1tNpmI'
        .'GxnYcNiV07mfqQ9y6fYxcVjx/W8LRm8aiuoG2DVGGBVpzpQdh4Cnzbu/wTnvUypnUs+ushL82xvdDsTMMfONa5J05/vktNnn2JmWEUG+IcPt5b'
        .'qbn/Sq7KSM0Wze2swE76p5L44PHZah79GyGENUTJ6kX1Hmz0llKw8meLvjvPd7/kEAv22Crecop0GZ1eEJZr+DsY+4jxFZSDQjPrFprn57sP78'
        .'kfQhsEbwMEeVP/sFKFQk22AGX9eoEDJrt98pT2/08oaTLHckmNlFbOiXPcvW4mPNlQfjXBfW4GTjhkoX/ueDu+m2ToiI3HG1ecXM9rezFvvuzV'
        .'z6ZDHnzt9M5Dy6UBH1gcPr8e0C8EnThoPn3+TFAfNWEkxSzKjkSXwEUiOKmV5tYz4HlCfkc4HZr1ew9biUpxIHXtasFz59yAsXk7WqPr29wvja'
        .'KdXb8nx3f/zSaDuPyd1TeL5hXn/XXN+f+VPTRi+NbArPBf4s/ga4c/Q0vLn74FE+w7JfbjmQ=='
    ))
];
$allowed = array_merge(array_keys($targets), array_keys($assets));
function inlinePath(string $root, string $rel): string {
    return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);
}
function inlineRead(string $path): string {
    $content = is_file($path) ? file_get_contents($path) : false;
    if ($content === false) throw new RuntimeException('Missing or unreadable: '.$path);
    return $content;
}
function inlineWrite(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Unable to create: '.$dir);
    if (file_put_contents($path, $content, LOCK_EX) !== strlen($content)) throw new RuntimeException('Write failed: '.$path);
}
function inlineReplace(string $content, string $extension, string $file): string {
    $present = [];
    foreach (['saas-searchable-select', 'saas-searchable-select-v2', 'saas-inline-search-select'] as $name) {
        foreach (["'", '"'] as $quote) {
            $needle = 'asset('.$quote.'assets/'.$name.'.'.$extension.$quote.')';
            if (str_contains($content,$needle)) $present[$needle] = substr_count($content,$needle);
        }
    }
    if (array_sum($present) !== 1) throw new RuntimeException('Expected exactly one select '.$extension.' asset() in '.$file.'; got '.array_sum($present).'. No change made.');
    $old = array_key_first($present);
    $new = str_replace(['saas-searchable-select-v2.'.$extension,'saas-searchable-select.'.$extension], 'saas-inline-search-select.'.$extension, $old);
    return str_replace($old, $new, $content);
}
try {
    if (!is_file(inlinePath($root, 'artisan'))) throw new RuntimeException('Place this patch beside artisan in your Laravel project root.');
    if (str_starts_with($mode,'--rollback=')) {
        $id = substr($mode, 11);
        if (!preg_match('/^inline_select_\d{8}_\d{6}_[a-f0-9]{6}$/', $id)) throw new RuntimeException('Invalid backup ID.');
        $backup = inlinePath($root, '.saas_backups/'.$id);
        $data = json_decode(inlineRead($backup.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        if (($data['patch'] ?? '') !== 'saas-inline-select-3' || !is_array($data['files'] ?? null)) throw new RuntimeException('Invalid backup manifest.');
        foreach ($data['files'] as $row) {
            $rel = $row['path'] ?? '';
            if (!in_array($rel,$allowed,true)) throw new RuntimeException('Unexpected backup path.');
            $current = inlineRead(inlinePath($root,$rel));
            if (hash('sha256',$current) !== $row['after']) throw new RuntimeException('Refusing rollback: '.$rel.' changed since installation.');
            if ($row['operation'] === 'modified') {
                $old = inlineRead($backup.'/original/'.$rel);
                if (hash('sha256',$old) !== $row['before']) throw new RuntimeException('Backup integrity failure: '.$rel);
            }
        }
        foreach (array_reverse($data['files']) as $row) {
            $rel = $row['path']; $file = inlinePath($root,$rel);
            if ($row['operation'] === 'created') {
                if (!unlink($file)) throw new RuntimeException('Cannot remove: '.$rel);
            } else inlineWrite($file,inlineRead($backup.'/original/'.$rel));
            echo "RESTORED  $rel\n";
        }
        echo "ROLLBACK OK; only inline-select changes reverted.\n";
        exit(0);
    }
    if (!in_array($mode,['--dry-run','--apply'],true)) {
        echo "Usage:\n php saas_inline_search_dropdown_patch.php --dry-run\n php saas_inline_search_dropdown_patch.php --apply\n php saas_inline_search_dropdown_patch.php --rollback=BACKUP_ID\n";
        exit($mode === '--help' ? 0 : 1);
    }
    foreach ($assets as $relative=>$bytes) if ($bytes === false || $bytes === '') throw new RuntimeException('Invalid embedded asset: '.$relative);
    $changes=[];
    foreach ($targets as $relative=>$extensions) {
        $original=inlineRead(inlinePath($root,$relative)); $updated=$original;
        foreach ($extensions as $ext) $updated=inlineReplace($updated,$ext,$relative);
        if ($updated !== $original) $changes[$relative]=['operation'=>'modified','before'=>$original,'after'=>$updated];
    }
    foreach ($assets as $relative=>$bytes) {
        $target=inlinePath($root,$relative);
        if (!is_file($target)) $changes[$relative]=['operation'=>'created','before'=>'','after'=>$bytes];
        elseif (inlineRead($target) !== $bytes) throw new RuntimeException('Existing asset differs: '.$relative.'. Nothing overwritten.');
    }
    if (!$changes) { echo "ALREADY APPLIED — no changes needed.\n"; exit(0); }
    foreach ($changes as $rel=>$row) echo ($mode==='--dry-run'?'WOULD ':'').strtoupper($row['operation'])."  $rel\n";
    if ($mode==='--dry-run') { echo "DRY RUN OK — ".count($changes)." files; no database changes.\n"; exit(0); }
    $id='inline_select_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
    $backup=inlinePath($root,'.saas_backups/'.$id);
    $manifest=['patch'=>'saas-inline-select-3','created_at'=>date(DATE_ATOM),'files'=>[]];
    foreach ($changes as $rel=>$row) {
        if ($row['operation']==='modified') inlineWrite($backup.'/original/'.$rel,$row['before']);
        $manifest['files'][]=['path'=>$rel,'operation'=>$row['operation'],
            'before'=>hash('sha256',$row['before']),'after'=>hash('sha256',$row['after'])];
    }
    inlineWrite($backup.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    $applied=[];
    try {
        foreach ($changes as $rel=>$row) {
            $target=inlinePath($root,$rel);
            if ($row['operation']==='modified' && inlineRead($target)!==$row['before']) throw new RuntimeException('File changed while installing: '.$rel);
            if ($row['operation']==='created' && file_exists($target)) throw new RuntimeException('File appeared while installing: '.$rel);
            inlineWrite($target,$row['after']);
            $applied[]=$rel;
            if (hash_file('sha256',$target)!==hash('sha256',$row['after'])) throw new RuntimeException('Checksum mismatch: '.$rel);
        }
    } catch (Throwable $e) {
        foreach (array_reverse($applied) as $rel) {
            $row=$changes[$rel];$target=inlinePath($root,$rel);
            if ($row['operation']==='created') unlink($target);
            else inlineWrite($target,$row['before']);
        }
        throw new RuntimeException('Installation aborted; changed files restored. '.$e->getMessage(),0,$e);
    }
    echo "APPLY OK — ".count($applied)." files.\nBACKUP: .saas_backups/$id\n";
    echo "ROLLBACK: php saas_inline_search_dropdown_patch.php --rollback=$id\n";
    echo "NEXT: php artisan view:clear && php artisan optimize:clear\n";
} catch (Throwable $e) {
    fwrite(STDERR,"ERROR: ".$e->getMessage()."\n");
    exit(1);
}
