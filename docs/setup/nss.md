# Unix users and groups from the database

penguinControl does not create Unix accounts. It stores them in its own tables
and lets glibc read them through an NSS module, so that `getpwnam`, `getgrnam`
and the shadow lookups all resolve against `user`, `user_info`, `group` and
`user_group`. This is what makes `AssignUserID` in the generated vHosts, and
`chown` in the system tasks, refer to real users.

## The module is no longer packaged

The original instructions used `libnss-mysql-bg`, which has been **removed from
Debian and Ubuntu** and is not available on any current release. The
configuration file format below is unchanged, so the options are:

* Build `libnss-mysql` from upstream source
  (<https://github.com/saknopper/libnss-mysql>) and install `libnss_mysql.so.2`
  into the system library directory. The two configuration files below are read
  as-is.
* Or replace this layer entirely — SSSD, or generating flat files consumed by
  `libnss-extrausers` from the same tables — at the cost of no longer resolving
  accounts live.

Whichever you choose, the queries below define what the panel expects to be
served, and are worth keeping as the reference.

`nscd` is still packaged, but is deprecated upstream; `unscd` is the maintained
drop-in replacement and is what these instructions now assume.

```
apt install unscd
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
systemctl restart unscd
```

## Verifying

```
getent passwd <username>
getent group <groupname>
id <username>
```

All three must resolve for a panel user before their vHost will start: Apache
fails to load a vHost whose `AssignUserID` names an account the system cannot
see.
