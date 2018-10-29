```
apt install libnss-mysql-bg nscd
```

# `/etc/libnss-mysql.cfg`

```
getpwnam    SELECT user_info.username AS username,'x' AS password,uid,gid,gcos AS gecos,homedir,shell \
            FROM user \
            INNER JOIN user_info ON user.user_info_id = user_info.id \
            WHERE user_info.username='%1$s' \
            LIMIT 1
getpwuid    SELECT user_info.username AS username,'x' AS password,uid,gid,gcos,homedir,shell \
            FROM user \
            INNER JOIN user_info ON user.user_info_id = user_info.id \
            WHERE uid='%1$u' \
            LIMIT 1
getspnam    SELECT user_info.username AS username,crypt AS password,user.lastchange,`min`,`max`,warn,inact,expire,flag \
            FROM user \
            INNER JOIN user_info ON user.user_info_id = user_info.id \
            WHERE user_info.username='%1$s' \
            LIMIT 1
getpwent    SELECT user_info.username AS username,'x' AS password,uid,gid,gcos,homedir,shell \
            FROM user \
            INNER JOIN user_info ON user.user_info_id = user_info.id
getspent    SELECT user_info.username AS username,crypt AS password,user.lastchange,`min`,`max`,warn,inact,expire,flag \
            FROM user \
            INNER JOIN user_info ON user.user_info_id = user_info.id
getgrnam    SELECT name,'x' AS password,gid \
            FROM `group` \
            WHERE name='%1$s' \
            LIMIT 1
getgrgid    SELECT name,'x' AS password,gid \
            FROM `group` \
            WHERE gid='%1$u' \
            LIMIT 1
getgrent    SELECT name,'x' AS password,gid \
            FROM `group`
memsbygid   SELECT user_info.username AS username \
            FROM user \
            INNER JOIN user_group ON user_group.uid = user.uid \
            INNER JOIN user_info on user.user_info_id = user_info.id \
            WHERE user_group.gid='%1$u' OR user.gid='%1$u'
gidsbymem   SELECT user.gid \
            FROM user \
            INNER JOIN user_info ON user_info.id = user.user_info_id \
            WHERE username =  '%1$s' \
            UNION ALL \
            SELECT user_group.gid \
            FROM user_group \
            LEFT JOIN user ON user_group.uid = user.uid \
            INNER JOIN user_info ON user_info.id = user.user_info_id \
            WHERE username =  '%1$s'

host        localhost
database    penguincontrol
username    nss-user
password    ***************
```

# `/etc/libnss-mysql-root.cfg`

```
username    nss-root
password    ***************
```

# `/etc/nsswitch.conf`

Make the following changes:
```
passwd:         compat mysql
group:          compat mysql
shadow:         compat mysql
```

When finished:
```
service nscd restart
```
