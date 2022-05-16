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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;500&display=swap" rel="stylesheet"> 

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="js/cubic-bezier.js"></script> 

    <!-- Styles -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="app">
        <div class="carousel-wrapper">
            <div class="slider">
                <div v-for="(pic, index) in pics" class="slide" :id="pic.id"
                     :style="{ transition: pic.isWin ? '0.2s' : transition + 's' }"
                     :class="{ 'slide-win': pic.isWin }">
                    <div style="position: relative">
                        <img :src="pic.path" class="slide-img" 
                            :style="{ 'border-radius': pic.isWin ? '0px' : '5px' }">
                        
                        <!-- <div class="slide-label" v-show="pic.isWin">
                            <div style="position: relative">
                                <img src="assets/logo.svg" alt="opensea-logo">
                                <span class="label-top">Цена на OpenSea</span>
                                <span class="label-price">$3 000.86</span>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            <img src="assets/glow.svg" alt="glow" class="glow-svg" v-show="isWin">
            <img src="assets/bg.svg" alt="background" class="background-svg">
        </div>
        <button @click="startSliding" style="font-size: 24px;">Roll</button>
    </div>

    <script>
        const app = new Vue({
            el: '#app',
            data() {
                return {
                    picSrcs: '<?php echo getPics()?>',
                    pics: [],
                    picsPath: '<?=$path?>',
                    position: 0,
                    SLIDESNUMBER: 5,
                    isSliding: false,
                    isWin: false,
                    transition: 0.05,

                    // Вероятности укзаываются тут, в виде:
                    // <название файла>: <процент выпадения. знак процента писать не надо>
                    probabilities: {
                        '1.jpg': 10,
                        '2.jpg': 10,
                        '3.jpg': 20,
                        '4.jpg': 10,
                        '5.jpg': 10,
                        '6.jpg': 10,
                        '7.jpg': 10,
                        '8.jpg': 5,
                        '9.jpg': 5,
                        '10.jpg': 10
                    }
                }
            },
            methods: {
                // Перелистывание слайдов
                shiftPics() {
                    // Сбрасываем счетчик при достиженни последней картинки
                    if (this.position == this.pics.length + 1) this.position = 1

                    // Плавно скрываем предыдущие слайды
                    this.pics[((this.position - 2) % this.pics.length + this.pics.length) % this.pics.length].id = 'slide-hidden'
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

                // Начало "игры"
                startSliding() {
                    if (!this.isSliding) {
                        // Сбрасываем стили картинки после предыдущей игры
                        this.pics[((this.position + 1) % this.pics.length + this.pics.length) % this.pics.length].isWin = false 

                        // Убираем общие стили предыдущей игры
                        this.isWin = false

                        // Перемешиваем картинки
                        this.pics = this.shuffle(this.pics)

                        // Скрываем все картинки для работоспособности перемешивания
                        this.pics.forEach(pic => {
                            pic.id = 'slide-hidden'
                        })

                        // Объявляем что карусель крутиться
                        this.isSliding = true

                        // Генериреум или ПОЛУЧАЕМ число оборотов и начинаем крутить
                        this.slide(Math.round(Math.random() * 20 + 50))
                    }
                },

                // Рекурсивный метод для анимации перелистывания
                slide(times = null, frame = null) {
                    if (!frame && frame != 0) {
                        // Первый кадр
                        this.shiftPics()
                        setTimeout(() => {
                            this.transition = this.getInterval(times, times)
                            this.slide(times, times - 1)
                        }, this.transition * 1000);

                    } else if (frame && frame != 0) {
                        // Последующие кадры
                        this.shiftPics()
                        setTimeout(() => {
                            this.transition = this.getInterval(times, frame)
                            this.slide(times, frame - 1)
                        }, this.transition * 1000);
                    } else if (frame == 0) {
                        // Конец анимации
                        this.transition = 0.05 //  Сбрасывам интервал перелистывания
                        this.isSliding = false // Объявляем что слайдер перестал крутиться
                        this.isWin = true // Выводим доп стили, при победе

                        // Применяем доп. стили к выигрышной картинке
                        this.pics[((this.position + 1) % this.pics.length + this.pics.length) % this.pics.length].isWin = true 
                    }
                },

                // Получения интервала между прокрутками. Должен замедлятся со временм
                getInterval(startFrame, currentFrame) {
                    // Конвертируем текущий кадр, в процент законченности анимации
                    let percentage = this.map(currentFrame, startFrame, 0, 0, 1) 

                    // Получаем тайминг-функцию при помощи кривых Безье.
                    // Используется opensource библиотека
                    // (https://github.com/gre/bezier-easing)
                    const timingFunction = bezier(.69,.01,1,.2)
                    
                    // Возвращаем интервал перелистывания в зависимости от % завершнности анимации
                    return timingFunction(percentage).toFixed(3)
                },
                
                // Функция масштабирующая число из одного диапазона, в другой. Тоже магия какая-то. 
                map(value, fromStart, fromEnd, toStart, toEnd) {
                    return toStart + (toEnd - toStart) * ((value - fromStart) / (fromEnd - fromStart))
                },

                // Сброс стилей
                reset() {
                    this.pics.forEach(pic => {
                        pic.isWin = false
                    })
                },

                // Генерация массива картинок на вывод, в соответствии с вероятностями
                generate() {
                    this.picSrcs.forEach(picPath => {
                        // Отделяем путь от названия файла, для того чтобы названия совпадали
                        // с ключами в объекте вероятностей
                        const picName = picPath.replace(this.picsPath, '')

                        // Проверяем задана ли вероятность для этой картинки
                        // Если нет выдаем ошибку
                        if (!this.probabilities[picName]) {
                            alert('ОШИБКА! Для одной из картинок не указана вероятность выпадения')
                            throw new Error('One of pics does not have probability')
                            return
                        }

                        // Проверяем равна ли сумма всех вероятностей 100. Ни больше, ни меньше
                        let sum = 0
                        
                        Object.values(this.probabilities).forEach(value => {
                            sum += value
                        })

                        if (sum != 100) {
                            alert('ОШИБКА! Сумма вероятностей не равна 100%')
                            throw new Error('Total value of all probabilities does not equal 100')
                            return
                        }


                        // Создаем n-ое кол-во копий картинки в зависимости от вероятности
                        for (let i = 0; i < this.probabilities[picName]; i++) {
                            this.pics.push({
                                path: picPath,
                                id: null,
                                isWin: false
                            })
                        }
                    })
                },

                // Перемешивание массива
                shuffle(array) {
                    return array
                        .map(value => ({ value, sort: Math.random() }))
                        .sort((a, b) => a.sort - b.sort)
                        .map(({ value }) => value)
                }
            },
            mounted() {
                // Конвертируем JSON который пришел из PHP в объект и получаем его значения в массив
                this.picSrcs = Object.values(JSON.parse(this.picSrcs))

                // Генерируем картинки на показ, в соответствии с вероятностями
                this.generate()

                this.pics = this.shuffle(this.pics)

                // Скрываем все картинки
                this.pics.forEach(pic => {
                    pic.id = 'slide-hidden'
                })

                this.shiftPics()
            }
        })
    </script>
</body>
</html>