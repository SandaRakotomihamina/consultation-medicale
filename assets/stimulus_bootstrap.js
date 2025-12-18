import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
import AnimationSlideshowController from './controllers/animation_slideshow_controller.js';
app.register('animation-slideshow', AnimationSlideshowController);
import ThemeController from './controllers/theme_controller.js';
app.register('theme', ThemeController);
