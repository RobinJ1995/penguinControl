```
apt install postfix-mysql
```

# MySQL permissions
```
GRANT USAGE ON *.* TO 'postfix'@'%' IDENTIFIED BY '***********';

GRANT SELECT, REFERENCES ON `penguincontrol`.`mail_domain` TO 'postfix'@'%';

GRANT SELECT (mail_enabled, user_info_id, uid, shell, id, gid, expire), REFERENCES (mail_enabled, user_info_id, uid, shell, id, gid, expire) ON `penguincontrol`.`user` TO 'postfix'@'%';

GRANT SELECT, REFERENCES ON `penguincontrol`.`mail_forward` TO 'postfix'@'%';

GRANT SELECT (username, id, validated, fname, lname, lastchange, email), REFERENCES (username, id, fname, lname, lastchange, email) ON `penguincontrol`.`user_info` TO 'postfix'@'%';

GRANT SELECT, REFERENCES ON `penguincontrol`.`mail_user` TO 'postfix'@'%';
```

# Postfix configuration
```
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf
```

## `mysql-virtual-alias-maps.cf`
```
user = postfix
password = **********
hosts = 127.0.0.1
dbname = penguincontrol
query = select destination from mail_forward inner join mail_domain ON mail_forward.mail_domain_id = mail_domain.id WHERE CONCAT(mail_forward.source, '@', mail_domain.domain) = '%s';
```

## `mysql-virtual-mailbox-domains.cf`
```
user = postfix
password = **********
hosts = 127.0.0.1
dbname = penguincontrol
query = select 1 from mail_domain INNER JOIN user ON mail_domain.uid = user.uid WHERE mail_domain.domain = '%s' AND user.mail_enabled = 1;
```

## `mysql-virtual-mailbox-maps.cf`
```
user = postfix
password = ****************
hosts = 127.0.0.1
dbname = penguincontrol
query = select 1 from mail_user INNER JOIN mail_domain ON mail_user.mail_domain_id = mail_domain.id WHERE CONCAT(mail_user.email, '@', mail_domain.domain) = '%s';
```
