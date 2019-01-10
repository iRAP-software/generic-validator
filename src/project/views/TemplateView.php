<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TemplateView extends AbstractView
{
    private $m_body;
    
    
    public function __construct(string $body)
    {
        $this->m_body = $body;
    }
    
    public function renderContent()
    {
?>


 <!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>iRAP Validator</title>
    
    <!-- Bootstrap core CSS -->
    <link href="/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="/libs/jquery/jquery-3.2.1.min.js"></script>
  </head>
  <body>
      <?= $this->m_body; ?>
  </body>
</html>



<?php
    }
}