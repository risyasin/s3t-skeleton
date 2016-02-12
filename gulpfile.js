/**
 * Created by yas on 15/12/15.
 */
"use strict";

var gulp        = require('gulp'),
    concat      = require('gulp-concat'),
    mincss      = require('gulp-minify-css'),
    uglify      = require('gulp-uglify'),
    jshint      = require('gulp-jshint'),
    head        = require('gulp-header'),
    shell       = require('gulp-shell'),
    del         = require('del'),
    bs          = require('browser-sync').create(),
    date        = new Date().toLocaleString(),
    src         = {},
    mods        = {'auth': {}, 'admin': {}};


gulp.task('clean:twigcache', function () {
    return del([
        'tmp/twig-cache/**',
        '!tmp/twig-cache/.keepme'
    ]);
});


/* ---------------- Auth module ------------------ */

mods.auth.css = {
    'files': [
        'bower_components/AdminLTE/bootstrap/css/bootstrap.min.css',
        'bower_components/font-awesome/css/font-awesome.min.css',
        'bower_components/Ionicons/css/ionicons.min.css',
        'bower_components/AdminLTE/dist/css/AdminLTE.min.css',
        'bower_components/AdminLTE/plugins/iCheck/all.css',
        'bower_components/AdminLTE/plugins/iCheck/square/blue.css'
    ],
    'dest': 'public/ui/modules/',
    'name': 'auth.all.min.css'
};

mods.auth.js = {
    'files': [
        'bower_components/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js',
        'bower_components/AdminLTE/bootstrap/js/bootstrap.min.js',
        'bower_components/AdminLTE/plugins/iCheck/icheck.min.js',
        'bower_components/SparkMD5/spark-md5.min.js'
    ],
    'dest': 'public/ui/modules/',
    'name': 'auth.all.min.js'
};


gulp.task('auth:css', function () {
    return gulp.src(mods.auth.css.files)
        .pipe(concat(mods.auth.css.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(mods.auth.css.dest));
});

gulp.task('auth:js', function () {
    return gulp.src(mods.auth.js.files)
        .pipe(concat(mods.auth.js.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(mods.auth.js.dest));
});


gulp.task('auth', ['auth:css', 'auth:js', 'clean:twigcache'], function () {
    console.log('Admin modules assets ready!');
});

/* ----------------- Admin module ----------------- */

mods.admin.css = {
    'files': [
        'bower_components/AdminLTE/bootstrap/css/bootstrap.min.css',
        'bower_components/font-awesome/css/font-awesome.min.css',
        'bower_components/Ionicons/css/ionicons.min.css',
        'bower_components/AdminLTE/dist/css/AdminLTE.min.css',
        'bower_components/AdminLTE/dist/css/skins/_all-skins.min.css',
        'bower_components/AdminLTE/plugins/iCheck/all.css',
        'bower_components/AdminLTE/plugins/iCheck/square/blue.css'
    ],
    'dest': 'public/ui/modules/',
    'name': 'admin.all.min.css'
};

mods.admin.js = {
    'files': [
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/AdminLTE/bootstrap/js/bootstrap.min.js',
        'bower_components/SparkMD5/spark-md5.min.js',
        'bower_components/AdminLTE/plugins/iCheck/icheck.min.js',
        'bower_components/twbs-pagination/jquery.twbsPagination.min.js',
        'bower_components/tinymce/tinymce.min.js',
        'bower_components/AdminLTE/dist/js/app.js'
    ],
    'dest': 'public/ui/modules/',
    'name': 'admin.all.min.js'
};

mods.admin.assets = {
    'src': ['bower_components/AdminLTE/dist/img/**'],
    'dest': 'public/ui/modules/img/'
};

gulp.task('admin:css', function () {
    return gulp.src(mods.admin.css.files)
        .pipe(concat(mods.admin.css.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(mods.admin.css.dest));
});

gulp.task('admin:js', function () {
    return gulp.src(mods.admin.js.files)
        .pipe(concat(mods.admin.js.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(mods.admin.js.dest));
});

gulp.task('admin:assets', function () {
    return gulp.src(mods.admin.assets.src)
        .pipe(gulp.dest(mods.admin.assets.dest));
});

gulp.task('admin', ['admin:css', 'admin:js', 'admin:assets', 'clean:twigcache'], function () {
    console.log('Admin modules assets ready!');
});


/* --------------  i18n dumper -------------------- */

gulp.task('i18n', shell.task([
    // Gettext can not read .twig files so we have to convert them to PHP files
    // dumping all slugs in twig files into temp directory > _DROOT/tmp/i18n-cache/
    'curl -L -s http://slim.dev/__dumpGettextStr', // i am too lazy to add http.client here. sorry bro.
    // Updating i18n/en_US.UTF-8/LC_MESSAGES/app.po file with missing slugs
    'xgettext --default-domain=app --output-dir=i18n/en_US.UTF-8/LC_MESSAGES --from-code=UTF-8 --omit-header -j -n -L PHP tmp/i18n-cache/*/*.php',
    // Updating i18n/tr_TR.UTF-8/LC_MESSAGES/app.po file with missing slugs
    'xgettext --default-domain=app --output-dir=i18n/tr_TR.UTF-8/LC_MESSAGES --from-code=UTF-8 --omit-header -j -n -L PHP tmp/i18n-cache/*/*.php'
    // duplicate the line above for your language if it's not english or turkish.
    // mkdir -p i18n/my_LN.UTF-8/LC_MESSAGES ## Change the language code.
    // cp i18n/en_US.UTF-8/LC_MESSAGES/app.po i18n/my_LN.UTF-8/LC_MESSAGES/. ## Change the language code.
]));





/* ---------------------------------- */

src.css = {
    'files': [
        'public/ui/bootstrap.css',
        'public/ui/app.css'
    ],
    'dest': 'public/ui/modules/',
    'name': 'auth.all.min.css'
};

src.js = {
    'files': [
        'public/ui/jquery-2.1.3.js',
        'public/ui/bootstrap.js',
        'public/ui/retina.js',
        'public/ui/app.js'
    ],
    'dest': 'public/ui/modules/',
    'name': 'auth.all.min.js'
};




/* ---------------------------------- */

gulp.task('css', function () {
    return gulp.src(src.css.files)
        .pipe(mincss())
        .pipe(concat(src.css.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(src.css.dest));
});

gulp.task('js', function () {
    return gulp.src(src.js.files)
        .pipe(concat(src.js.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(src.js.dest));
});



gulp.task('js-uglify', function () {
    return gulp.src(src.js.files)
        .pipe(uglify())
        .pipe(concat(src.js.name))
        .pipe(head('/* created at '+date+' */'+'\r\n'))
        .pipe(gulp.dest(src.js.dest));
});



gulp.task('lint', function () {
    return gulp.src(src.app.files)
        .pipe(jshint())
        .pipe(jshint.reporter('default', { verbose: true }));
});


gulp.task('reload-css', ['css'], function () {
    bs.reload();
});


gulp.task('reload-js', ['js'], function () {
    bs.reload();
});


gulp.task('watch', ['css', 'js'], function () {
    gulp.watch(src.css.files, ['reload-css']);
    gulp.watch(src.js.files, ['reload-js']);
});




gulp.task('default', ['auth'], function () {
    bs.init({proxy: 'http://slim.dev'});

    var at = require('always-tail'),
        tail = new at('./tmp/app.log', '\n');
    tail.on('line', function(data) { console.log(data); });
});



gulp.task('build', ['css', 'js'], function () {
    shell('http://localhost/pagespeed_global_admin/cache?purge=*');
    console.log('NGX pagespeed cache cleared!');
    console.log("\r\n"+'Build complete.');
});