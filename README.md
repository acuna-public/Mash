# Mash
 
Mash is a powerful framework for websites, API and CLI applications building.

Application example:

**1.** Create your application template in `templates/Default/main.htm` folder for web site:

```html
<html>
  
  <head>
    {headers}
  </head>
  
  <body>
    {content}
  </body>
  
  {footers}
  
</html>
```

**2.** Create your project main file like `index.php` in the root folder of your project:

```php
<?php
  
  /**
   * WebSite is your application type. Another application types
   * is API for API, and CLI for console applications, or Mash
   * for build your own application type for your purposes
   */
  
  class MySite extends WebSite {
    
    public function getRootDir () { // Your project root dir
      return __DIR__;
    }
    
    public function setConfig (): Mash\Config { // Your project config
      return new MySite\Config ($this);
    }
    
    public function onInit () {
      
      parent::onInit ();
      
      // Your app initialization, for example connect to new DB, etc.
      
    }
    
    public function onShow (): string {
      
      // Your site content
      
      $this->tpl->load ('main'); // Load your HTML template
      
      $this->tpl->set ('content', 'Hello World!'); // Change its tags
      
      $this->tpl->compile ('main'); // Compile your template
      
      return $this->tpl->result['main']; // Return compiled content
      
    }
    
  }
  
  $site = new MySite ();
  echo $site->show ();
```

Done, your site is ready, now you can create new templates for another modules and combine them. `{headers}` and `{footers}` tags will be replaced by **Mash** which added needed HTML tags automatically, for example `title`, `meta`, etc.

*Another documentation will be soon*
