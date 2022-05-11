<?php 
    // Путь до картинок
    $path = "pics/";

    // Функция для получения путей до картинок. В идеале это должен делать полноценный
    // бэкенд, а все пути должны лежить в БД с шансами и всем таким
    function getPics() {
        global $path;
        $pics = array_diff(scandir($path), array(".", ".."));
        
        // Приписываем названияем картинок путь
        $pics = array_map(function ($pic) {
            global $path;
            return $path.$pic;
        }, $pics);

        return json_encode($pics);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider</title>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        .slider-img {
            width: 264px;
            height: 299px;
            object-fit: cover;
            object-position: center;
            position: absolute; 
            transition: 0.25s;
        }

        .slider {
            transform-style: preserve-3d;
            perspective: 1000px;
            position: relative;
            height: 299px;
            display: flex;
            justify-content: center;
        }

        #slide0 {
            transform: translate3d(-120%,0,-200px) rotateY(45deg);
        }

        #slide1 {
            transform: translate3d(-65%,0,-100px) rotateY(45deg);
        }

        #slide3 {
            transform: translate3d(65%,0,-100px) rotateY(-45deg);
        }

        #slide4 {
            transform: translate3d(120%,0,-200px) rotateY(-45deg);
        }

        #slide-to-hide {
            transform: translate3d(-180%,0,-300px) rotateY(45deg);
            opacity: 0;
        }

        #slide-to-show {
            transform: translate3d(180%,0,-300px) rotateY(45deg);
            opacity: 0;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="slider">
            <img v-for="(pic, index) in pics" :src="pic.path" :id="pic.id" class="slider-img" >
        </div>
        <button @click="randomise">Shift</button>
    </div>

    <script>

        const app = new Vue({
            el: '#app',
            data() {
                return {
                    pics: '<?php echo getPics()?>',
                    position: 0,
                    picsToShow: null,
                    SLIDESNUMBER: 5
                }
            },
            methods: {
                // Перелистывание слайдов
                shiftPics() {
                    // Сбрасываем счетчик при достиженни последней картинки
                    if (this.position == this.pics.length + 1) this.position = 0

                    // Плавно скрываем предыдущий слайд
                    this.pics[((this.position - 1) % this.pics.length + this.pics.length) % this.pics.length].id = 'slide-to-hide'

                    // Определяем массив выводимых изображений при помощи магической формулы
                    let result = []
                    for (let position = this.position, i = 0; position < this.position + this.SLIDESNUMBER; position++, i++) {
                        this.pics[(position % this.pics.length + this.pics.length) % this.pics.length].id = 'slide' + i
                    }

                    // Готовим к показу следующий слайд
                    this.pics[((this.position + this.SLIDESNUMBER) % this.pics.length + this.pics.length) % this.pics.length].id = 'slide-to-show'

                    // Сдвигаем позицию и пишем результат
                    this.position++
                },

                randomise(times = null) {
                    let x
                    if (!times && times != 0) {
                        x = Math.round(Math.random() * 5 + 10)
                    } else {
                        x = times
                    }
                    if (x != 0 ) {
                        this.shiftPics()
                        setTimeout(() => {
                            this.randomise(x - 1)
                        }, 300); 
                    } else {
                        alert('you won a nigger')
                    }
                }
            },
            mounted() {
                // Конвертируем JSON который пришел из PHP в объект и получаем его значения в массив
                this.pics = Object.values(JSON.parse(this.pics)).map(pic => {
                    return {
                        path: pic,
                        id: null
                    }
                })

                this.shiftPics()
            }
        })
    </script>
</body>
</html>