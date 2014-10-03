TableTop
=======

TableTop Web Application. Node/Express/Angular App hosted to Firebase and connected to AngularFire. 
Built With Yeoman, [generator-angularfire](https://github.com/firebase/generator-angularfire).

Dependencies - If not already installed, do this first!
---------
- Node - `brew install -g node`
- Bower - `npm install bower`
- Grunt - `npm install grunt`

Setup Instructions
---------
- `git clone https://github.com/stcho/coursekarma.git`
- `npm install`
- `bower install`
- `sudo gem install compass`

Running Locally
---------
- `grunt serve`

Build/Minify to tabletop/dist
----------
- `grunt build`

Deployment
----------
Make sure to change firebase references in `firebase.json` as well as `app/scripts/angularfire/config.js` before deploying.

- `firebase deploy`
- `firebase open`

Firebase Reference
----------
- Production - [https://tabletopdinner.firebaseio.com/](https://tabletopdinner.firebaseio.com/)
- Staging - [https://tabletopstaging.firebaseio.com/](https://tabletopstaging.firebaseio.com/)