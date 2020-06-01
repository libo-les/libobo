<?php
return [
    // api版本
    'api_vs' => 0.2,
    'appid' => 'wx7472b2a17d92fcf8',
    'secret' => '762c5ea62985f369df9a63167ac3274f',
    'mch_id' => '1539275771',
    'is_wss' => '0',
    // 微信凭证
    'access_token_debug' => 1,
    // 授权码设置
    'access_token' => [
        // 'type'   => 'md5',
        'type' => 'ssl',
        'public_key' => "-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJuT7hA8rDdF4te7YevdYSwn1g\nxxkw4HV/cPz6b4mFidPV/XJJ3z2jtv6Rw7GtCyh0kxg04Z6CgQ3WNqAnJUzcGiqr\nUCLxQyLE62/CdZzA591+97KQKBHSPRwnv69Qzk07tdBOfvhjF3ak5NxMRfdAKQ1P\nmwnNf/Y/OvAavrS2EwIDAQAB\n-----END PUBLIC KEY-----\n",
        'private_key' => "-----BEGIN PRIVATE KEY-----\nMIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMm5PuEDysN0Xi17\nth691hLCfWDHGTDgdX9w/PpviYWJ09X9cknfPaO2/pHDsa0LKHSTGDThnoKBDdY2\noCclTNwaKqtQIvFDIsTrb8J1nMDn3X73spAoEdI9HCe/r1DOTTu10E5++GMXdqTk\n3ExF90ApDU+bCc1/9j868Bq+tLYTAgMBAAECgYBV2hU8xtRg4wHB8cjMxJZ7XXLm\n4VWDS8Q1/Oxt2VJ6FvKlPDyL3ArrxlIJz3Oq5fjNxFylBPDlMe+ln5IBlwpiT/Gf\nmm4rRN/IZFGuDqGKccJOZg6hoxghhLziikXSyqAjuJHNdt95QvM1OOz3M26skvcq\nwReuOhp1/SECR6MlQQJBAPxeMGTOrcjs8SUoSVm+cW7ecurJ0MHldc8C+dUJv7YU\nISvHNYc3SFZ42yjJmHs9FAktOPswNm+omM73DZ2V6ZsCQQDMoHcjJ0ZGv05SW2xU\ncmalHlM70ahwCGnShe4UkdeUXBHU6oibHmQe+PC5AXuUobivBZfjfV+JhgD1urfN\nZsjpAkEAqWM+8CqZEoeWw+3qbcYZtOSyFU0oRTySekPxFEDa4IdaTFwFXaCJeSDd\nWN8W7YDtdctYt6CAqSlsh13jUaZRiQJBALThs+6zLQAk48sRXbVYWzvijpj2P/qo\ntUKPeWFdej9/E5QurgrQ1pg6XjBsCx3bxzGPtDA4B1e9yONu5kL/dOECQAQ+0EYk\n2CCqLs20oQ+Dk0orT+UnaLL5umPGAq2HbmQkZ+TLZSeBufJt9YyEvA4U2hQaxcPX\nuIZ+RpzBfp0eTog=\n-----END PRIVATE KEY-----\n",

    ],
    // 微信开放平台信息
    'component_dll_saas' => [
        'component_appid' => 'wx8fad8b6e167661eb',
        'component_appsecret' => '616b3dc115c144430a51be6325a98b10',
        'component_token' => 'lexinxiang2018lexinxiang2018lexi',
        'component_key' => 'lexinxiang2018lexinxiang2018lexilexinxiang2',
    ],
    // 微信服务商
    "service_provider" => [
        '1520797141' => [
            'appid_version' => 'service_v1',
            'app_id' => 'wx7a91b462afd2ae55',
            'mch_id' => '1520797141',
            'key'    => 'wertlsdfertettyrt5486dfgertearfg',   // API 密钥         
        ],
        '1533092741' => [
            'appid_version' => 'service_v2',
            'app_id' => 'wx66e5b17e2aee3af6',
            'mch_id' => '1533092741',
            'key'    => 'jinhuakeji142222198601010633yang',   // API 密钥         
        ],
    ],
    // 小程序服务器域名
    "wx_domain" => [
        'requestdomain' => ['https://d1.huilianshi.cn', 'https://d2.huilianshi.cn', 'https://d1t.huilianshi.cn', 'https://wx.qlogo.cn', 'https://apis.map.qq.com'],
        'wsrequestdomain' => ['wss://d1.huilianshi.cn', 'wss://d1t.huilianshi.cn'],
        'uploaddomain' => ['https://d1.huilianshi.cn', 'https://d2.huilianshi.cn', 'https://d1t.huilianshi.cn'],
        'downloaddomain' => ['https://d1.huilianshi.cn', 'https://d2.huilianshi.cn', 'https://d1t.huilianshi.cn', 'https://wx.qlogo.cn'],
    ]
];