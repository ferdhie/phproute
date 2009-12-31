<?php

function current_path( $include_query_string = FALSE )
{
    $current_path = $_SERVER['REQUEST_URI'];
    
    if (isset($_SERVER['QUERY_STRING']) && !$include_query_string)
        $current_path = str_replace( "?$_SERVER[QUERY_STRING]", '', $current_path );

    if ($current_path != '/') 
        $current_path = rtrim($current_path, '/');

    return $current_path;
}

class Controller
{
    /** 
    this is for querystring style dispatch 
    eg: http://localhost/x/?m=edit
    */
    function dispatch($director='m')
    {
        if ($director)
        { 
            $this->director = $director;
        }
        
        $methodname = g( $this->director );
        
        if (method_exists( $this, $methodname ))
        {
            $this->$methodname();
        }
        else
        {
            $this->__default__();
        }
    }
    
    function routes($urls=false)
    {
        if (!$urls) 
        {
            $urls = isset($this->urls) ? $this->urls : array();
        }
        $this->root = dirname($_SERVER['PHP_SELF']);
        $this->path = str_replace($this->root, '', current_path());
        $this->path = ltrim($this->path, '/');
        $error = '';
        
        foreach($urls as $pattern => $app)
        {
            if (preg_match($pattern, $this->path, $match))
            {
                if (is_array($app))
                {
                    if (is_object($app[0]))
                    {
                        if ( method_exists( $app[0], $app[1] ) )
                        {
                            return $app[0]->$app[1]( &$this, $match );
                        }
                    }
                    else if (class_exists($app[0]))
                    {
                        $obj = new $app[0];
                        if ( method_exists( $obj, $app[1] ) )
                        {
                            return $obj->$app[1]( &$this, $match );
                        }
                    }
                    else if (file_exists($app[0].EXT))
                    {
                        include_once $app[0].EXT;
                        if (class_exists($app[0]))
                        {
                            $obj = new $app[0];
                            if ( method_exists( $obj, $app[1] ) )
                                return $obj->$app[1]( &$this, $match );
                        }
                    }
                }
                //function call
                else if (function_exists($app))
                {
                    array_unshift($match, &$this);
                    return call_user_func_array($app, $match);
                }
                //simple inclusion
                else if (is_file($app))
                {
                    include( $app );
                    return;
                }
            }
        }
        
        header( "HTTP/1.1 404 Not Found" );
        $this->show404("
            <p>Unknown controller</p>
            <pre>
Request URI: $_SERVER[REQUEST_URI]
PATH: $this->path
</pre>
        ");
        exit;
    }
    
    function load_template($_template, $params=array())
    {
        extract( $params, EXTR_OVERWRITE );
        ob_start();
        include TEMPLATEPATH . $_template;
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }
    
    function render($template, $params=array())
    {
        echo $this->load_template($template, $params);
    }
    
    function show404($message='')
    {
        if (!$message) $message = '<p>The page you are looking for is not found</p>';
        header( "HTTP/1.1 404 Not Found" );
        echo <<<EOD
<html><head>
<title>404 Page Not Found</title>
<style type="text/css">
body { background:#fff; margin:40px; font-family: Lucida Grande, Verdana, Sans-serif; font-size:12px; color:#000; }
#content  { border: #999 1px solid; background-color: #fff; padding: 20px 20px 12px 20px; }
h1 { font-weight: normal; font-size: 14px; color: #990000; margin: 0 0 4px 0; }
</style>
</head>
<body>
    <div id="content">
        <h1>404 Not Found</h1>
        $message
    </div>
</body>
</html>
EOD;
        exit;
    }
    
    function __default__()
    {
        $this->show404();
    }
}

/* End of Controller.php */