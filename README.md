[![PHP](https://img.shields.io/badge/PHP-8.2.4-brightgreen)](https://www.php.net/archive/2023.php#2023-03-16-2)
[![Symfony](https://img.shields.io/badge/Symfony-6.2.8-brightgreen)](https://symfony.com/blog/symfony-6-2-8-released)



![Logo](https://raw.githubusercontent.com/Zzzenk/article_generator/main/public/img/logo_with_title.png)

> Graduation work for [Skillbox](https://skillbox.ru/course/symfony/) (course PHP-framework Symfony)

*Create a unique article perfect for your business, website or project in a couple of clicks!*


## Features

- different themes (thech, hames and more!)
- insert promoted words into the generated article
- upload article images
- get access via API
- more features with PRO subscription


## Installation


```bash
  doctrine:database:create
  doctrine:migrations:migrate
  doctrine:fixtures:load
```
    
## API Usage/Examples

**Link**
`/api/article`

**Request example**
```html
POST {{ link }}
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{ token }}

{
   "theme": "tech",
   "title": "Article title",
   "keywords": {
       "keyword0": "EXAMPLE",
       "keyword1": "EXAMPLE",
       "keyword2": "EXAMPLE",
       "keyword3": "EXAMPLE",
       "keyword4": "EXAMPLE",
       "keyword5": "EXAAAAAMPLE",
       "keyword6": "EXAMPLES"
   },
   "sizeFrom": 1,
   "sizeTo": 1,
   "word1": null,
   "word1Count": null,
   "word2": null,
   "word2Count": null,
   "images": ["https://via.placeholder.com/250x250"]
}
```


## API Reference


| Parameter    | Type     | Description                      |
| :----------- | :------- | :------------------------------- |
| `theme`      | `string` | **Required**! Article theme      |
| `title`      | `string` | **Required**! Article title      |
| `keywords`   | `string` | 0-5: Russian cases, 6: plural.   |
| `sizeFrom`   | `int`    | Qty of templates (min 1)         |
| `sizeTo`     | `int`.   | Qty of templates (max 5)         |
| `word1`      | `string` | 1st promoted word                |
| `word1Count` | `int`    | Qty of 1st promoted word         |
| `word2`      | `string` | 1nd promoted word                |
| `word2Count` | `int`    | Qty of 2nd promoted word         |
| `images`     | `array`  | Image links (separate by commas) |




## Tech Stack

**Language:** PHP 8.2.4 + Symfony 6.2.8

**Server:** Apache/2.4.56 (Unix), MySQL 8.0.32


## Environment Variables

To run this project, you will need to update the following environment variables to your .env file

`DATABASE_URL`

`MAILER_DSN`


## Author

- [@zzzenk](https://www.github.com/zzzenk)

