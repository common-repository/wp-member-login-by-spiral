=== WP Member Login by SPIRAL ===
Contributors: pipedbits
Donate link: 
Tags: member, login, authentication, security, user
Requires at least: 5.7
Tested up to: 6.5.3
Stable tag: 1.2.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add membership management and secure authentication by SPIRAL&reg; into your WordPress site.

== Description ==

“WP Member Login by SPIRAL”は スパイラル株式会社 が提供するクラウド型ローコード開発プラットフォームSPIRALを利用し、安全な会員サイトを制作できるプラグインです。プログラム開発は一切不要。どなたでも簡単に会員サイトを作成することができます。
プラグインの特長
・ログインフォームをウィジェットとして表示
・会員データは高セキュリティなSPIRALのデータベースにて安全に保管されます。
・SPIRALの会員データへの認証とセッション管理が自動で行えます。
・会員サイトを実現する複数のショートコードを提供
　ショートコードを利用してWebコンテンツページをログインにより表示内容の切り分けができます。
　また、会員属性によってもコンテンツ内容の表示の切り分けが可能です。
　さらに、SPIRALのデータベースに保管されているデータをコンテンツページへ表示も可能です。
プラグインの設定方法やショートコード情報などは[サポートサイト](https://apl-support.pi-pe.co.jp/wpmls/)を参照してください。
※SPIRALは スパイラル株式会社 の登録商標です。

バージョン1.0.7以前をご利用中の場合、バージョン1.1.0以降に実装された機能を利用するためには設定変更が必要です。
詳細は [バージョン1.0.7以前をご利用中でアップデートを行う方へ](https://apl-support.pi-pe.co.jp/wpmls/wpmls_ver110-info/) をご確認ください。

このプラグインは会員管理機能を提供するために第三者（外部）サービスを利用しています。
プラグインを利用するには、[SPIRAL ver.1](https://www.pi-pe.co.jp/spiral-series/spiral-suite/) または [SPIRAL ver.2](https://spiral.pi-pe.co.jp/)の契約が別途必要です。


"WP Member Login by SPIRAL" is a plugin that utilizes the cloud-based low-code development platform SPIRAL provided by SPIRAL Corporation, enabling the creation of secure membership sites. No programming is required, and anyone can easily create a membership site.
Plugin Features:
Display the login form as a widget.
Member data is securely stored in SPIRAL's high-security database.
Authentication to SPIRAL's member data and session management are automatically handled.
Provides multiple shortcodes to realize a membership site.
You can use shortcodes to differentiate the display content of web content pages based on login.
Additionally, content display differentiation based on member attributes is possible.
Furthermore, it is also possible to display data stored in SPIRAL's database on content pages.
For information on plugin settings and shortcodes, please refer to the [support site](https://apl-support.pi-pe.co.jp/wpmls/).
*SPIRAL is a registered trademark of SPIRAL Corporation.

If you are using version 1.0.7 or earlier, configuration changes are necessary to utilize the features implemented in version 1.1.0 and later. For details, please check information for [those using version 1.0.7 or earlier and updating](https://apl-support.pi-pe.co.jp/wpmls/wpmls_ver110-info/).

This plugin utilizes third-party (external) services to provide membership management functionality.
To use the plugin, a separate contract with [SPIRAL ver.1](https://www.pi-pe.co.jp/spiral-series/spiral-suite/) or [SPIRAL ver.2](https://spiral.pi-pe.co.jp/) is required.

= External Service Usage =
[SPIRAL ver.1](https://www.pi-pe.co.jp/spiral-series/spiral-suite/)
[SPIRAL ver.2](https://spiral.pi-pe.co.jp/)


== Service Provider: Spiral Platform ==
- **Service URL:** [SPIRAL ver.1](https://www.pi-pe.co.jp/spiral-series/spiral-suite/)
- **Terms of Use:** After logging into the administration panel, please check from the administrative menu.

- **Service URL:** [SPIRAL ver.2](https://spiral.pi-pe.co.jp/)
- **Terms of Use:** [SPIRAL ver.2 Terms of Use](https://www.pi-pe.co.jp/area/table_file/B1-K8B000270010O9k0E0E24000002000hj5)

= Usage Details =
This plugin uses the Spiral API to enhance its features. The API requests are made to the following endpoints:

1. API Endpoint:
   - **Endpoint URL:** `https://api.spiral-platform.com/v1`

Please note that by using this plugin, you acknowledge and agree to the terms of use and privacy policy of Spiral.
Make sure to review them to ensure compliance and understanding of how your data is handled.


== Privacy Policy ==

WP Member Login by SPIRAL is designed as part of the creation and functionality of membership websites.
You can connect your membership website created with this plugin to the low-code development platform SPIRAL and  sets the websites to send data to the database in SPIRAL accounts and also can decide which accounts the data is sent to.
the data is managed by who managed the accounts. SPIRAL Inc. processes without anyone seeing or touching the data.
If you have any questions regarding privacy, please feel free to [contact us](https://www.pi-pe.co.jp/regist/is?SMPFORM=man-mcsepb-0e06c81b1a06832e44b64b391bc18b71&f000099823=WordPress%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%20%E4%BC%9A%E5%93%A1%E7%AE%A1%E7%90%86&f000099824=https://www.pi-pe.co.jp/solution/wp-mls/).

== Installation ==

Install "WP Member Login by SPIRAL" automatically from your WordPress Dashboard by selecting "Plugins" and then "Add New" from the sidebar menu. Search for "WP Member Login by SPIRAL", and then choose "Install Now".

Or:

1. Upload `member-login-by-spiral.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the "Plugins" menu in WordPress

= Getting Start =

SPIRAL&reg; account is required to use this plugin. If you have not already registered for an account, [register a trial account on our site](https://www.pi-pe.co.jp/regist/is?SMPFORM=man-nasao-b6f7c53cb1ae2b6cf70b778b1ed3fbcb).

To enable plugin and see "WP Member Login by SPIRAL" menu added to the "Settings" menu below.  And then open this settings page, enter your SPIRAL-API token, authentication-form url and link settings.

For more information see the [Plugin Manual for Japanese](https://stage-apl-support.pi-pe.co.jp/wpmls/) (or [English version](http://www.microsofttranslator.com/bv.aspx?from=&to=en&a=http://developer.pi-pe.co.jp/trial-navi/wpmls-manual.html) by Microsoft WPMLS_Translator).

== Frequently Asked Questions ==

= What is SPIRAL&reg;? =

SPIRAL&reg; is a cloud platform as a service (PaaS) from SPIRAL Inc. that developers use to build web applications using a combination of web components based on database.

== Screenshots ==

1. plugin settings
2. widget settings
3. login widget

== Changelog ==

= 1.2.6 =
* Tested with WordPress 6.5.3
* Added new features
* Security updates
* Fixed bugs

= 1.2.5 =
* Tested with WordPress 6.2.2
* Fixed bug

= 1.2.4 =
* Tested with WordPress 6.1.1
* Shortcode enhancements
* Fixed bug

= 1.2.3 =
* Tested with WordPress 6.1
* Shortcode enhancements
* Fixed bug

= 1.2.2 =
* Fixed bug

= 1.2.1 =
* Tested with WordPress 6.0.2
* Enhance securityd
* Fixed bug

= 1.2.0 =
* Tested with WordPress 6.0.1
* Enhance security
* Add shortcode
* Fixed bug
* Improved performance with api cache

= 1.1.3 =
* Tested with WordPress 5.9.2
* Fixed bug

= 1.1.2 =
* Tested with WordPress 5.8.3
* Enhance security
* Add shortcode

= 1.1.1=
* Fixed bug

= 1.1.0 =
* Tested with WordPress 5.8.1
* Enhance security
* Add shortcode

= 1.0.7 =
* Tested with WordPress 5.4.1

= 1.0.6 =
* Fix for PHP7.1 compatibility

= 1.0.5 =
* Enhance security

= 1.0.4 =
* Remove test code

= 1.0.3 =
* Update SPIRAL's area API parameter
* Fix for PHP5.2 compatibility

= 1.0.2 =
* Fixed minor bug

= 1.0.1 =
* Fixed behavior of shortcode

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
* Initial release
