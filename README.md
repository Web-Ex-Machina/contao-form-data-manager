Form Data Manager for Contao
======================

Stores the submissions of Contao forms, with statistics (completion percentage, delays, pages) and the
[Personal Data Manager](https://github.com/Web-Ex-Machina/contao-personal-data-manager) integration.

Requirements
---

- PHP 8.4+
- Contao 5.7+
- Optional: `terminal42/notification_center` ^2.0 for the `##useful_data##` tokens

Usage
---

Enable "Store via Form Data Manager" in the form settings. The form needs a mandatory `email` field: it is
created (or made mandatory) when the form is saved.

Submissions are listed in *Content > Contacts* and under each form, and can be exported to CSV.

Notification Center tokens (form generator notifications):

| Token | Description |
| --- | --- |
| `##useful_data##` | Submitted data (HTML), without the technical fields |
| `##useful_data_text##` | Submitted data (text), without the technical fields |
| `##useful_data_filled##` | Filled submitted data (HTML), without the technical fields |
| `##useful_data_filled_text##` | Filled submitted data (text), without the technical fields |

Development
---

```bash
composer install
vendor/bin/ecs check --fix
vendor/bin/rector process
vendor/bin/phpstan analyse
```
