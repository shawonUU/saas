<?php
/**
 * Global Searchable Select V4 - exact bug-fix for the 2026-11-28 SaaS project.
 * Root cause: V3 CSS `inset:auto!important` overrode the JS `top` and `left`.
 * Result: options existed, but the popover appeared at viewport (0,0).
 * V4 anchors the listbox to the editable field, including modals.
 *
 * php saas_search_dropdown_v4_fix.php --dry-run
 * php saas_search_dropdown_v4_fix.php --apply
 * php saas_search_dropdown_v4_fix.php --rollback=BACKUP_ID
 */
declare(strict_types=1);
$root = __DIR__;
$mode = $argv[1] ?? '--help';
$views = [
    'resources/views/portals/partials/theme-styles.blade.php' => ['css'],
    'resources/views/portals/partials/ui-controls.blade.php' => ['js'],
    'resources/views/public/business/public/ecommerce/layout.blade.php' => ['css','js'],
];
$assets = [
    'public/assets/saas-inline-search-select-v4.css' => gzuncompress(base64_decode(
        'eNq9V9tu2zgQfc9XcBMUiAvTsWz5EgldLFAsFujuW4G+FH2gqJHFtSQKJBXbNfLvO6Rk6+K4yV6wKJCaGl6GZ86cGT68J79lMmIZEUUmCqAamOIp4TKPZCT3' .
        '5IsfEHMogcRCATfZAScSkwJ5ElpEGZBEQBZPyPuHm4f35MucRNUGv+0hDkgBT6BIpQHXaDABq4z8SeSlVIYVJiDCEIkzlIyBfPpMjCwfMkiM20xDhsdNNGOa' .
        'OmdowYx4gmMsdJmxQ1DIAtrNnm86U4+l1MIIWQQKMrcqPK1qbplksA9ZJjYFFQZyHWijwPA0tIbAIx6xvoY7EZs08KbTd2HO9rQ7FEUznIZ4BSM4y6jbMMhF' .
        'HGcQcplJheeloETfvSBgiQF15LIwgDjc3oZnh1mkZVYZCJXYpCbwFuU+RFwC3J7fL6bvCCXzcj9qPFuhNQU30/40ihU6kSoPlDTMwL2/iGEzCiOpYlC02XKy' .
        'LPcETxEx4ZVS6MFH6+ppViSNkfn1abJkXJhDMFks0G2BV1AU41wY7ULSuynpxk8UZWXO4Ysyybdt/PD0PdXiuyg2wdmRfcfeAbzztY1Id2ovVB1DL7i9BWoj' .
        'it7GJYtj9KXBbI6Yd4ysLDFLWMFhwMKQ7iDaCkOvz4gY326UrIqYipxtLuyItUbeGNibzlc7pDZXkkzuAsgyUWqhwwQZ9CLFLoEPEskrfZSVsQkQzNrg2qjS' .
        'XOzvMbG12kTjJ6buKS0V+qcO47vVYulDNCLIvrFjWMksG0ZhsxeVSWKze1rHMGUxujgl9h/C9vb9vcH+vQt174MUYqg88fFMxeUJtkIi9hliBPGry09peN5l' .
        'vngFw69MCYY/nzDR4w+3RlVw++3Y0LXO9zuYLtgyuSJMNIeiatXJyWQnyq1KvpoO39GNGNk88/yVv54v/VXH2MuxH7A7WPRofeIXPQyU2lk0VzLLaAQpexJ4' .
        'U6teTBSNbATemVB1eCtBa0sT7p2mlizNqDHdzR991KjRaNRTglqtWCwqHXhez8c2fYLzOSUrIGuP6Q51pRLGT6fuUhT78V2SJKP+kXXszhvabGv364xiprbj' .
        'Oy+ZPc5XQ6c71PfWCIaP4BKkO7tfjb35eL4YT2br7hKbvDbGEHiYJ2GNb8RUo10mRXAbkAOMJmYb0eaAlaXVN1cVUOSedlgXPMzp0SXdvqZYj6D49pbC6RbY' .
        'JLoPSlnaqFNZQjFyn4bzaMKsv3w7etPWsrScP091FfjPShuRHOipEmLqc0CKmR1A0avPHGyhCTesRFFHtBpSW9ZNz3R+xJHlS+hkqSmM3sQfUMqWykYvmgIW' .
        '7nACjRSwbeD+UvshdHyhzim8mspZFmIzo2jdm1yWu+aOE6Ep47btGF8ag9TCeuzw+HWB9JczSFAg/b5AXiFwu265XAEbveRirWT1PaCVMkfJXdNQTKfXV540' .
        '9LzyLKL+4jUtpvbe5fEUNBtOFzVie51OUninYePQejoNMzC237AhsWsnUx/y3uVz7J3i8d0aHtcsHtV1s22LKizLijM9CBtPgW97l3/Es17GtI5Ffz3kpTm0' .
        'F5qdaPgDxzr3xOnPN6nJs68xM4xib53Dh1srNbffyEXxqNmiuZ0V2En/VBLvPD5bzaN/I4SwhihZvajeg43eUgpW/mzRd+f55pccYsHuW8VbTpEuo+MLwnIJ' .
        'fwdjHzG+gHJQaGbdQvP8bJ8zfwhtELwNEORN/cNKFQo12QKU2j2EYI/Jbh9Mpf3/I0qazLFcUiOllXPiHHdvmglPNpRfTHCfm4ETDlnonzvejm8mGTpi4/HG' .
        'FUfX6doOzJsve/Wz6YsHXzsd89BybrsHFofPr3v0C0EnDpqPnz/bx54mjKSYRfhU1CVwkQhOaqW51vw/T8jvCKdDs37lYStRKQ6krl0teO6cKzA2L0ZrdN2a' .
        '+6VRtLPLV+T45r/ZZBCXv7P6atG84Ly/rjn/v7KHRgxfGJgV/kv8GXzt8GdoafnTt2CC/QX+s/dg'
    )),
    'public/assets/saas-inline-search-select-v4.js' => gzuncompress(base64_decode(
        'eNq1PE1v5UZyd/+KnjksyZ0njmSvDUOyRhjPjNfeeGYUazw+KAK2RfbT44qPpEk+fexYhxySPSyCHBabQ7BALoHGCAJkkUOOyV8R8ktSH93NbpLvw1oE2LXI' .
        'ZnV1VXV1ffabxz8XR1IeiazIs0KJRsk6mcnTXIlCttmF2mpUrpJWyFRWrarF21+IH8SH2x9+srWzs/Xhp/EH4ufiNUxUadbStKScn5an5RVgrBbtRLx6DUiT' .
        'skhlfa3Ri2mm8jQWb2ZKlHV2lhUyF5/xQk8QYa3mMisa0QJAUy7qBOCmoq0XLcwta3Eh8ywF8spigu9z0SxO51nTwICQRSrUVda0WXEmfnWEBD7+IAwjsf9E' .
        'vPtAiGDRANK2zpI22IP3bCrCy6xIy8v4lbqSR0ThEdFyELNU3qoaUUdAV7uoC5wFDDUtoJGtasS+KNSl+E7J85eyCqPuew4C1F+PVOt9kUDdvgjTMlnMVdHG' .
        '5uFFruidAH74QQSqCKK4Lb8uL1X9TDYqjGJYtm6b77J2Fgan8FkcCPwrdgm6W+SyrFOkjhfb32eoAxKD0HsBc+5u//Hu9p/ubv/77vbHu/e/u7v9nbi7/c+7' .
        '2//B0ff4An/+9u4W/vcnevgxmAg1r9prmvzHu/e/p5m/B8i/u7v9t7vb/4J3QU+A8w937/8FHuD9P/AdX34E5IBjXtaKUPz93e2fAbA//5aoAcr+lcnwyFxP' .
        'I3GZzMqy4VV+JPA/373/h7vbfyc8/8zr/JFGNWNlhXrV0IxxapCAAJDfgMB7onxzXSkxU7USbalHHVm9KsVctskMNVMv00mBpsKsQtZ1eUmqX6tmkbdNn5Mj' .
        'fSILjcQj+mgGkw1yJLJTh7m8eps1GR7SfbHzyTZ+yRVosfp+oYoER+2YTFqtu4s8N4OVKlKg/bBsMsQPX6cyb1S3QpIricNwPhcKD9wRnLPiLOT3A1BT0OUC' .
        'Diyc39+qMHj1xV8909qdyFy5Og4T53hgAPd0USS0HlukN+qqDdlYRFr+vDpwDWvzl5j/qPQ1i+J4+2SPQPG4P0BIOFvwJ9ak4uEIzAGHRwbWrwjXwqrPyqLF' .
        's8m0xbWqcpmo8PHfNI8en01EIAI64TdDmg8RcFbmqap7pOslNNVnqn3aAvrTRQviARMnt6puKhAIVGtQ2LP6mlWhrMOAt/yY2Nl/+PAkiA5GaMb5ZBVi1qYB' .
        'uVnzbYNmfBWVYNnBAoHCgE3+8s3Lr5kIbbjEz34mHmgS56C9WQXqBmNmW2DfxWegfh4cHgrVhMExcew5npMJD/Jh0oP7D8vpFHkc0M/eQm96SPa5x8PTupbX' .
        '8bQu5/xZa0qsz0wE1FRhyG8TYDVVV+Q7QsYieMgcuQnr+q5+ZXWagMU9Vbkd3FB3JnqBNKMtSO18M4C793lZ4iHTBMZJDrvYtLT/Z3W5qI4NMEjHYpxlaaoK' .
        'i49fN8LGoC4uGreoKll3Tgs0Tp69knN9nl4fvvnlN6+/PUSXMwYek5TQawWE+yaK4mmWQ5QRZq2ao9Af4IOmd7jZRG5o9pdONtst11N7vhrsA0PsDS0cY2Cd' .
        'APIWnVruVmVVXqh6q6zQG0fCAQLa1CF/Zv8u3K9JLpvmawhGYMPnABMGjZTNFsVIWwixBRY0P5XJeTCcrLdpHwMf5X6lyAr01rUVss7klrqqIPxRKXiEgEyz' .
        'j5XnMSX9qSyOVDUJGHlZtP7MWXY2y+H/aGC3dvrS0icoa0DJC7K6IKLrItHnb3hI7beJqMDJqfpC/TWaM+NRvC1dvoa7yS6H9rzsC2+yGR+TyfeLrKYZ7NLE' .
        '48fiFdkhY/R0zKmaPZG1DVgBGhAzEHgOcfEU4jc4O+S3L7SXZdyDxRyLjuo3dBAOzdFG+27Ih33XHtfj23yOIscHeoKPvBW0T/T87TKaZiUod6fmbXl2lvtq' .
        'bqQOxI3uRrREnewugqcwh3Vf72hkzn43dwgCpsaKczLG4P5yFjGkxoB6MGuozZUOh6whWhEmLTFGPWVHy/zAXdnT+neWd3MAiB6DukZl3fcIh6ji83JBRD3L' .
        'M7C83wCMkR3PusggR9kXOhECBV7I/C2MVWXdenBvygoDPPh0EIMTBmXEESB42wP7UmlrwZAzfsXog1fIgJ2agbx5n5dtW85xHqJ9ZBB5MN9lKeSBBvUlvfUw' .
        'E0gnVpRJfMqoP2PUMIFGW3h+YtZdK9ymgmP6ucpLFNZL2c7ASVyF2xNL+JZw19oSO9uemGn+09OSHI8739KyxeQNZko9yaHgMwjiP6bYqsP6xAFwp8Mydkvs' .
        'up/CwvySFeFH2/AW8jIHLspdB2UEhH0UeYRd6t2wiCx64ok+T8TOxx9Hk25leJ2YjQRWP/FR5mrq0+mQSUgRwJl/qf9+6qMBCzdVNdi9HueAxfGyTVKXef6l' .
        'VdAdlIoVl4ewJd03IrLkaVX9xNvF/uJb4uMIRGkp6BTmI7P9WmkeASSvCj7oqzmeQPDHu+RZ3n4EB/s6V81MQT6WKjC86LV+DRG5anfloi0fZGbGrycGyeUs' .
        'S2YCQ5S6TJX41RFy8hiFuEdYUQ5CVpUiZJKtAWIRqJrbUWwQvbiq8izJWvHs6Oj1S+AxK2uwceD2zhayhqlnWLYBycsziN8rlWTTLBEdSUwDRExN3A92iC30' .
        'bYc1RFl1ex0GSCA4DdKGRyKorjCssbhGAqYRHMAozGppg+6HgZQL5rGS3RcL6MkWG8HA0a57o6s1pgC3/B7zWdU2RbDSvdNxAAz0d+gdIU1dqMO+i+QIpOcn' .
        'rZ83UGOutIuFMaMD7E37tMjmVAz8oobcIzT+WPNx06fIxrImMtAJHRsCvYRfWUggyOPgz3pWHeM5rrsDAvtUnLUz9Caj4fOoaykgBuk5BWssNIWDFdB2RksD' .
        'dcQ42EuvbPA0zyHpBrYVZNSUoWFSHU/L+oVMZiEkZ7po6ogCAj5KSl4t5qcQXak8xvwctCuu0T9QUGUXFgK+D9Una3S+AXrDCCMDj6LUQ+ui3kHKMsHVMhNQ' .
        '3ni2O1nUmHra/RsKw5QfgI39hwEcTdoROKGm0KBjVFYTUFaNMjIP2pV8VbQlhk7hO3Gal8k55LcFWtamDTRNnjr6Uer3Og3CYpSrgecKR6m+FhKMxxxmyaSa' .
        'I9WPQUqNqMDP0YCOhB9Q+Qt5CnkJ+kbpeQTyT/IFSDmEiVRAckCoGNADiXyNvLAlR6JyPMO3uUDcgINRIbtgXa0cGiRdPXk2y/IU5BeuzVWpelmri6xcNL9E' .
        'mgd5/wOmzp5cT+mpgAtTbLU+qRUspesYYZBmF4HVYAJmpedaiHDNJX0N9jSUUxnC6Jsqc/TFIHOYRgddpCF9NioOCt8oSyy2RsJOI7BnwlyNCbXjkQXQ7Sfq' .
        'gfOGyuHJzp1pJDRTEg31pjKiohRPWSopWh0kZeB8WXUEujiH4tKznZVv7FNfI8Zw6qSKAv4NWQPgpUzpir0PnKW+WYJ3MDsadgtNEVFGXsCf6VtFNOToy/Ui' .
        '0SpYsqCmSN7VDbqFukw67pXUv6KaaA+7cQJ67hDfEniWrFe10BYDLEXBa4VXaCmuHLoctOu5dAoQLlXW5kT93ea65PL9hnwIpctwY3pJH3y6tDqyTd0bnryN' .
        'ZD5y8JKZSs7X0+rOwxlLFZS+wqljKJ+34H//9Af7aUTQXLBEDcQQyl/VkQHNHz2Qw+OLMYWBvPnAl5mNgp64ds2RkU59y1ptfngReq3xJqAx240f9lawg997' .
        '/HjlBb3b/SjTOQrGb26uNE7oYtA/2YesbhgIm+8TXYXlmb0IfhDAYFF8efiyvKzXFbnW1Xad8t6DJRVAW82njzZ91iXceZnKXKSZzMuzBpTlXIly0TaQiIrn' .
        'kMdmBWRFsTjEBCjH7hZ+wMSYZ9g8GoYKgdcHdD8A88qtXF7DU1Oa/AF2spWIscF4vSixK5pVppcbu/UJWu+bsmz7hWrTh3nIBByjiE8eUvBl1fi0TK9H2xZe' .
        'j4W9t10ocp6NTnZTV7QgnArmSMDq6Fh7XWFf0E0/Z+Wl7pBwX8hoTtCd1Ra05t2SSViLM0c/wZaMD9llFzJNV/ZXzIHjoOleKH5qE8a1g9XyIwSLzTM/Hx1t' .
        'bY/0K48Jeklz2z1r+L66WzjSNEtmsjgbNlI8A0Mq5sQmKyA9OO/83rf9wDOo+QL5FgZ0oPRHdBJ3KZG3aSAnlsROp3d9y1Shfr1AHCFe2uGngBaB7YSEbnEK' .
        '0mos6mjvJyHi9VdhGlQrVDHDVnuvH88ZS69XPzSafD8pnsmmg3nntN8abA2YTxNtRA80G+xX+BEyAmt4GMrYF+t8PFy8L0uK6Ngy2jBi6bpLQ28ceBkwrrcC' .
        'KW+hUQVSGrRUiBH9eOB+cNcLrUU2YwdiMPQI2/fUx47w2bEfvKyLHOtt8K2CjJRWL6dTCCi0MalUnpuAzrG4Y6bGxPrmup3P21i1xFkYJ+Zg79ZOAs0BX7eo' .
        'zIRNFlrahNa9rNRuI9+u06LCNCd89MhcRlq3CgXSwWT84owD4V6ZoZHmID7ePll2OUZDFrit9q4M3z1ZQVGbtbQbI+A6BsUK+0YxaOeQRuJP/Bg4cD4VOipB' .
        'uc9lsZC5h3Mod+1BSPJG7EuRG33racGyiwpL9w3DI8BFF9+YrK7ZgfdBC6/pPl9QRxNvg/JlULzn2YI/eWza8Sl4c5LiRGCsJfVlU74WyvdM9RW97tCOunpe' .
        '2Pp4Bm/lqfFcppRkgtWigaDxafobmXTRFvA4bRUGSMAe2i7XjOlwS1tGGveCOZsjdJGY3yx+p9dm1BNzwRbhJ+bOwa44Ppl0kf0ukD0R5Sl1+utdqnnxlUBh' .
        'rHZjrbbukWNiC5IgAXUXOET/RoeVBMCRe0OpKoh7HTfHt27d6x6cWKxDYBzu/eeTemBB+IKcVFfFpoFYBwrP1VRCVI4RphtIwGuX1NhvI8rcreNFen7l2VQT' .
        'hkaoc9RcFrBEcmFhRLB5BmHoCFc6ImDmWlmDOTTB3kEcBrHjNSO/irCZPAa5+o0Tr1jXNqCXptt9tEFXX5H48F9XWEDUpV2+SGPvcbPs6ILifK4gK2pVfo09' .
        'Ocj+i3J4wRwsVGyv6/Hm5c6dB+Htr7dny1gxovdZGc9MB8h1zWLDuyfRKM3rCPTPzDuP+/ErX0Z7PSJXcaNNjnMRhjs8jeolhUOwddSfq+u0vCzGdBupYi2l' .
        'JghmkE/xivRzhEcnPfbx2ypw9XyZlvN4A3k89kblmewStJ+4wWKknNJrRzxaxceB2BForbuUQuepI+y/gKilDvoF/CGZbjrwF0nB6Sn0KlPHPSZPqLXkF690' .
        'lQ5J38Hw2Zu/fSLYLY0URaN+YjwsJq8QUpPISgXjt8furxl+2rqKgDfyNOhVqdadgtN8UQ+OMNjGwzLDLXfMIN017FONPx84V6riy4ii5JjJZAjGIFI972UG' .
        'OXIrm3Pzy5jRO3Qr8j5O+jGU6W5M3fTY5MrngMuKuVl/3jUg/TKCRDovgW/a0jFPN9LKjpbt7yZ0LvW2bk9oQ0JcE/sAZ+L9s/JyLG+x7QpupuvIoneY/YOh' .
        'e/GDPkzUY5SPngkH9S+TXi5aUu7Xejhc6uL+gruVm9yqtM5kECCM028ebPj6DvODtlaKqyp4tyDLU9zQbkDWwIyqn4OYzKA0G6CrMeamux3/glrWEFoHTisp' .
        'cJpnQdf5MHlpQHwFJx3tHP5hXnIwomx4G9cJeVX7JpurctGG/Rg4whtZY9cIpoBiBjpQunUQt2CkS7MMgZrVUDcMdcuf1LsXgV/dm8P0Pqg14ai5Mo8xJ/NL' .
        'R1CXsBw8BNy/iuLO6u6g8OTIX4jMT8ej0/hmuwVs5t5FIjux3yzhgwjU6Lo90t6Lxtz8Y3ASlpTtmkuww/a602b0LW+GIG1Z0V18Gj+TPc/UOyygugljRFPt' .
        'XN7Wv0ywg/qGhRmkXDBVWDYKx1i2bNWoQq7tMCpp98nNitYZobkeaMauIJmitPfDSnPPg0lP/RKatwcGN26DXacfTpkPuk6I56UzFMFYK5ayvG4auQS3BUqO' .
        'LR8/I5onSrq9BrGZRhT0CrQY++au4vrfAe1I784LW1wmHAEVeE3UEQ6SpdJXMNr4jDN1CB7jf8hT49HcGepsD3ytFBAq8ifTxI2sRsNX11x8MBR5+G425F3r' .
        'k+Z+yC/HtXh3ahljuFFD2j3WI0dt3SuOTsY91GOtBkCsbWx0bV7fgvTaCK4h0QtHxmaNGBn8rR4/dr8o6H5J0ISRvTc1Gu4OHPayXz2jCx/47J5Pv6e31h66' .
        '88ddXHufAHUoGG5V6LMYumFh5IJxfXcUrC+9Cd9EXUsuRfvZBqS6q9Exdff0nkSuJ4/74sHEN0Yec/qnG6NRUfZb1Z/rz/J/rHLw/4ZlnA+OxPA/S/4RASrc' .
        'ev+OwK4IfhFvx9vBxLjICQt1QoWFXXOu7T76ZeBht22vCx+6O8erCprku12ij6RsviIaLdFL2DH/ZILd8FrJ9PqIaUMnmZd04Q6s3gqdeP76pe69fA3g5rdh' .
        'NRmAEuyU17s17fqaHP1NhP/9P+ALBf8='
    )),
];
$allowed = array_merge(array_keys($views), array_keys($assets));
function is4Path(string $root, string $rel): string {return $root.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$rel);}
function is4Read(string $path): string {
    $data = is_file($path) ? file_get_contents($path) : false;
    if ($data === false) throw new RuntimeException('Missing/unreadable file: '.$path);
    return $data;
}
function is4Write(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir,0775,true) && !is_dir($dir)) throw new RuntimeException('Cannot create directory: '.$dir);
    if (file_put_contents($path,$content,LOCK_EX)!==strlen($content)) throw new RuntimeException('Write failure: '.$path);
}
function is4Ref(string $source, string $extension, string $rel): string {
    $hits=[];
    foreach (['saas-searchable-select','saas-searchable-select-v2','saas-inline-search-select','saas-inline-search-select-v4'] as $name) {
        foreach (["'",'"'] as $quote) {
            $needle='asset('.$quote.'assets/'.$name.'.'.$extension.$quote.')';
            $found=substr_count($source,$needle);
            if ($found) $hits[$needle]=$found;
        }
    }
    if (array_sum($hits)!==1) throw new RuntimeException('Expected exactly one searchable-select '.$extension.' asset in '.$rel.'; found '.array_sum($hits).'. Existing code not changed.');
    $old=array_key_first($hits);
    $new='asset('.(str_contains($old,'asset("')?'"':"'").'assets/saas-inline-search-select-v4.'.$extension.(str_contains($old,'asset("')?'"':"'").')';
    return str_replace($old,$new,$source);
}
try {
    if (!is_file(is4Path($root,'artisan'))) throw new RuntimeException('Put this patch in your Laravel root, beside artisan.');
    foreach ($assets as $rel=>$data) if ($data===false || strlen($data)<100) throw new RuntimeException('Embedded asset invalid: '.$rel);
    if (str_starts_with($mode,'--rollback=')) {
        $id=substr($mode,11);
        if (!preg_match('/^inline_select_v4_\d{8}_\d{6}_[a-f0-9]{6}$/',$id)) throw new RuntimeException('Invalid backup ID.');
        $backup=is4Path($root,'.saas_backups/'.$id);
        $manifest=json_decode(is4Read($backup.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
        if (($manifest['patch']??'')!=='saas-inline-select-v4') throw new RuntimeException('Wrong backup manifest.');
        foreach ($manifest['files'] as $row) {
            if (!in_array($row['path'],$allowed,true)) throw new RuntimeException('Unsafe path in backup.');
            if (hash('sha256',is4Read(is4Path($root,$row['path'])))!==$row['after']) throw new RuntimeException('Refusing rollback; changed after install: '.$row['path']);
            if ($row['action']==='modified' && hash('sha256',is4Read($backup.'/original/'.$row['path']))!==$row['before']) throw new RuntimeException('Invalid backup checksum: '.$row['path']);
        }
        foreach (array_reverse($manifest['files']) as $row) {
            $path=is4Path($root,$row['path']);
            if ($row['action']==='created') {if (!unlink($path)) throw new RuntimeException('Cannot remove: '.$path);}
            else is4Write($path,is4Read($backup.'/original/'.$row['path']));
            echo 'RESTORED  '.$row['path'].PHP_EOL;
        }
        echo "ROLLBACK OK. Only V4 changes reverted.\n";
        exit(0);
    }
    if (!in_array($mode,['--dry-run','--apply'],true)) {
        echo "Usage:\n php saas_search_dropdown_v4_fix.php --dry-run\n php saas_search_dropdown_v4_fix.php --apply\n php saas_search_dropdown_v4_fix.php --rollback=BACKUP_ID\n";
        exit($mode==='--help'?0:1);
    }
    $changes=[];
    foreach ($views as $rel=>$extensions) {
        $before=is4Read(is4Path($root,$rel));$after=$before;
        foreach ($extensions as $ext) $after=is4Ref($after,$ext,$rel);
        if ($after!==$before) $changes[$rel]=['action'=>'modified','before'=>$before,'after'=>$after];
    }
    foreach ($assets as $rel=>$data) {
        $target=is4Path($root,$rel);
        if (!is_file($target)) $changes[$rel]=['action'=>'created','before'=>'','after'=>$data];
        elseif (is4Read($target)!==$data) throw new RuntimeException('Refusing to overwrite a modified V4 asset: '.$rel);
    }
    if (!$changes) {echo "ALREADY APPLIED - nothing to change.\n";exit(0);}
    foreach ($changes as $rel=>$row) echo ($mode==='--dry-run'?'WOULD ':'').strtoupper($row['action']).'  '.$rel.PHP_EOL;
    if ($mode==='--dry-run') {echo 'DRY RUN OK - '.count($changes)." files; no migration.\n";exit(0);}
    $id='inline_select_v4_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
    $backup=is4Path($root,'.saas_backups/'.$id);
    $manifest=['patch'=>'saas-inline-select-v4','timestamp'=>date(DATE_ATOM),'files'=>[]];
    foreach ($changes as $rel=>$row) {
        if ($row['action']==='modified') is4Write($backup.'/original/'.$rel,$row['before']);
        $manifest['files'][]=['path'=>$rel,'action'=>$row['action'],'before'=>hash('sha256',$row['before']),'after'=>hash('sha256',$row['after'])];
    }
    is4Write($backup.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    $done=[];
    try {
        foreach ($changes as $rel=>$row) {
            $path=is4Path($root,$rel);
            if ($row['action']==='modified' && is4Read($path)!==$row['before']) throw new RuntimeException('Changed before installation: '.$rel);
            if ($row['action']==='created' && file_exists($path)) throw new RuntimeException('Unexpected file appeared: '.$rel);
            is4Write($path,$row['after']);$done[]=$rel;
            if (hash_file('sha256',$path)!==hash('sha256',$row['after'])) throw new RuntimeException('File checksum mismatch: '.$rel);
        }
    } catch (Throwable $e) {
        foreach (array_reverse($done) as $rel) {
            $row=$changes[$rel];$target=is4Path($root,$rel);
            if ($row['action']==='created') @unlink($target);
            else is4Write($target,$row['before']);
        }
        throw new RuntimeException('Patch failed; completed changes restored. '.$e->getMessage(),0,$e);
    }
    echo 'APPLY OK - '.count($done).' files.'.PHP_EOL;
    echo "BACKUP: .saas_backups/$id\n";
    echo "ROLLBACK: php saas_search_dropdown_v4_fix.php --rollback=$id\n";
    echo "NEXT: php artisan view:clear && php artisan optimize:clear\n";
    echo "Then hard refresh (Ctrl+F5); V4 filenames eliminate stale V3 cache.\n";
} catch (Throwable $e) {
    fwrite(STDERR,'ERROR: '.$e->getMessage().PHP_EOL);
    exit(1);
}
