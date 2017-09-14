#Mantis LdapAvatar plugin README

## Presentation

This plugin connects to the [Mantis globally configured LDAP](https://www.mantisbt.org/docs/master/en-US/Admin_Guide/html-single/#admin.config.auth.ldap) and cache the avatars retrieved from a configured LDAP attribute to a local storage path.  

When the user's LDAP last modified attribute is modified, the avatar is retrieved again from LDAP.

If the avatar exceeds the configured maximum width or height, the avatar is resized. 

## Configuration

The configuration is available via the Mantis manage plugins admin page.

**Avatars storage path**

The path where the avatars from LDAP are cached.  
Defaults to *[this plugins folder]/files*.

**LDAP avatar attribute**

The name of the LDAP attribute where the avatar is stored (as bytes).  
Defaults to *jpegphoto*.

**LDAP last modified attribute**

The name of the LDAP attribute where to check for a modification on the LDAP user.  
Defaults to *modifytimestamp*.

**Avatar maximum width**
 
The maximum width an avatar can have without being resized.  
Defaults to *80* pixels.

**Avatar maximum width**
 
The maximum width an avatar can have without being resized.  
Defaults to *80* pixels.

##Todo list

- [X] Documentation (README.md, code)
- [X] Configuration page
- [X] Post issue on MantisBT for fixed avatar width/height on View Issue Details
- [ ] Tests with AD LDAP
- [ ] Use alternate LDAP database for avatars?
- [ ] Continuous integration
- [ ] Add options to change output images options (jpeg, compression ratio, ...)
- [X] Delete old versions of avatars
