# drupalcommerce-remibit
DRUPAL COMMERCE REMIBIT MODULE 

INSTALLATION AND CONFIGURATION


In order to install the module, it is necessary to access the server where the web files are hosted by ssh. If you don’t know how to do that, please contact your site administrator or your hosting provider.

In this example we will be using the default Drupal Commerce configuration, so the website files are located in /var/www/html/mystore. Please replace <mystore> with the actual name of your website directory.

1/. Go to the drupal directory and then to the directory where you have to place the module files.

```
cd /var/www/html/mystore
cd web/modules/custom
```

2/. Fetch the RemiBit module

```
sudo wget https://github.com/RemiBit/drupalcommerce-remibit/releases/download/plugins/drupalcommerce-remibit.zip
```

3/. Uncompress and install it

```
sudo unzip drupalcommerce-remibit.zip

sudo drupal moi remibit
```


4/. The RemiBit module is now installed and activated. To verify that, please go to your drupal site administration panel. 

Clic on Extend
Scroll down to COMMERCE (CONTRIB)

RemiBit Payment Method is ticked, which means it’s already activated.


5/. Now go to Commerce > Configuration > Payment > Payment gateways

Click on Add payment gateway
Select ``REMIBIT Payment Method``
Click on ``Name`` and write ``RemiBit``
Click on ``Machine-readable name`` and write ``remibit``

6/. Fill up ``Login ID``, ``Transaction Key``, ``Signature Key`` and ``MD5 Hash`` with the data you get from your RemiBit account’s Settings > Gateway
.
Click on Save and you’re done.

