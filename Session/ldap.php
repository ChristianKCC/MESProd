<?php
define('DOMINIO', 'kcc.com');
define('DN', 'dc=kcc,dc=com');
function mailboxpowerloginrd($user,$pass){
   $ldaprdn = trim($user).'@'.DOMINIO; 
     $ldappass = trim($pass); 
     $ds = DOMINIO; 
     $dn = DN;  
     $puertoldap = 389; 
     $ldapconn = ldap_connect($ds,$puertoldap);
       ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION,3); 
       ldap_set_option($ldapconn, LDAP_OPT_REFERRALS,0); 
       $ldapbind = @ldap_bind($ldapconn, $ldaprdn, $ldappass); 
       if ($ldapbind){
     $filter="(|(SAMAccountName=".trim($user)."))";
         $fields = array("SAMAccountName","mail"); 
         $sr = @ldap_search($ldapconn, $dn, $filter, $fields); 
         $info = @ldap_get_entries($ldapconn, $sr); 
     $array = array($info[0]["samaccountname"][0],$info[0]["mail"][0]);
     }else{ 
          $array=0;
       } 
     ldap_close($ldapconn); 
   return $array;
} 

?>
