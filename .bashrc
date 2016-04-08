function herogit() {
    git add .
    git commit -a -m "$1"
    git push heroku
}

