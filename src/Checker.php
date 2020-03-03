<?php
namespace Astrviktor\Tools\Checker;


// класс для разных проверок
class checker
{
    
    // Пустой конструктор
    public function __construct()
    {
    }

    // Проверка email на валидность и MX
    // https://space-base.ru/library/?book=105
    // https://habr.com/ru/post/434640/
    //
    public function checkEmail($email)
    {
        //$email = mb_strtolower($email);
        $str = "";

        if (preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email))
        {

            $str = 'Адрес ' . $email . ' - правильный формат.';

            $domain = substr(strrchr($email, "@"), 1);
            $res = getmxrr($domain, $mx_records, $mx_weight);

            if ( $res ) 
            {  
                $str = $str . ' MX запись ' . $mx_records[0] . ' ' . $mx_weight[0];
                return ['email' => $email, 'status' => 'OK', 'info' => $str];
            }

            $str = $str . ' ' . $domain . ' не удалось проверить MX запись.';
            return ['email' => $email, 'status' => 'Not OK', 'info' => $str];
        }

        $str = "Адрес " . $email . " - неправильный формат.";
        return ['email' => $email, 'status' => 'Not OK', 'info' => $str];
    }

    // Проверка списка email
    public function checkEmailList($emailList)
    {
        $emailListStatus = [];

        foreach ($emailList as $email)
        {
            $emailStatus = $this->checkEmail($email);

            //$emailListStatus = $emailListStatus + $emailStatus;
            array_push( $emailListStatus, $emailStatus);

        }

        return $emailListStatus;
    }



    // Проверка скобочек
    // Выражений вида "string=(()()()()))((((()()()))(()()()(((()))))))"
    // Проверить на длинну и пустоту
    // - если строка корректна, то пользователю возвращается ответ 200 OK, 
    // с информационным текстом, что всё хорошо;
    // - если строка некорректна, то пользователю возвращается ответ 400 Bad Request,
    // с информационным текстом, что всё плохо.
    public function checkParenthesis($len, $str)
    {
        $strlen = strlen($str);
        
        if ($strlen == 0)
        {
            return ['answer' => 400, 'info' => 'Указана пустая строка'];
        }

        if ($len != $strlen)
        {
            return ['answer' => 400, 'info' => 'Указана длинна ' . $len . ' на самом деле ' . $strlen];
        }

        $checkstr = substr(strrchr($str, "="), 1);

        $res = 0;
        $idx = 0;
        foreach (str_split($checkstr) as $char)
        {   
            $idx++;

            if ($char == '(') $res++;
            if ($char == ')') $res--;
            if ($res < 0) 
            {
                return ['answer' => 400, 
                        'info' => 'Скобочная структура ' . $checkstr . ' сломана на позиции ' . $idx];
            }
        }

        return ['answer' => 200, 'info' => 'Все хорошо c ' . $checkstr];
    }
  
}
