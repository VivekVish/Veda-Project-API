<?php
    define("ERROR_0","Query is invalid.");define("DEBUGERROR_0","Query is invalid.");
    define("ERROR_1","Unable to load autosave.");define("DEBUGERROR_1","Unable to load autosave in ContentAutosave::loadFromUri().");
    define("ERROR_2","The functionality for quiz discussion has yet to be built.");define("DEBUGERROR_2","Quiz discussion called in ContentAutosave::loadFromUri() but functionality is not built yet.");
    define("ERROR_3","Unable to autosave.");define("DEBUGERROR_3","Unable to insert new autosave in ContentAutosave::save().");
    define("ERROR_4","Unable to autosave.");define("DEBUGERROR_4","Unable to update autosave in ContentAutosave::save().");
    define("ERROR_5","Unable to delete old autosave entries.");define("DEBUGERROR_5","Unexpected element_type in ContentAutosave::deleteOldEntries().");
    define("ERROR_6","Unable to delete old autosave entries.");define("DEBUGERROR_6","Unable to delete old autosaves in ContentAutosave::deleteOldEntries().");
    define("ERROR_7","Unable to delete old autosave entries.");define("DEBUGERROR_7","Unable to delete previous autosaves in ContentAutosave::deleteOldEntries().");
    define("ERROR_8","Unable to delete old autosave entries.");define("DEBUGERROR_8","Unable to delete autosave in ContentAutosave::deleteSavedEntry().");
    define("ERROR_9","Unable to set ILOs for autosave.");define("DEBUGERROR_9","Unable to generate SimpleXMLElement in Lesson::setILOs().");
    define("ERROR_10","Invalid HTML.");define("DEBUGERROR_10","Invalid HTML in ContentAutosave::loadFromPayload().");
    define("ERROR_11","Unable to load course.");define("DEBUGERROR_11","Unable to load from payload in Course::loadFromPayload().");
    define("ERROR_12","Unable to load course.");define("DEBUGERROR_12","Unable to load Course from URI in Course::loadFromUri().");
    define("ERROR_13","Unable to save course.");define("DEBUGERROR_13","Unable to save course in Course::save().");
    define("ERROR_14","Unable to delete course.");define("DEBUGERROR_14","Unable to delete course in Course::delete().");
    define("ERROR_15","Unable to save discussion.");define("DEBUGERROR_15","Unable to generate SimpleXMLElement in Discussion::loadFromPayload()..");
    define("ERROR_16","Unable to load discussion.");define("DEBUGERROR_16","Unable to load discussion in Discussion::loadFromId().");
    define("ERROR_17","The functionality for quiz discussion has yet to be built.");define("DEBUGERROR_17","Quiz discussion called in Discussion::loadFromId() but functionality is not built yet.");
    define("ERROR_18","Unable to load discussion.");define("DEBUGERROR_18","Invalid element type in Discussion::loadFromId().");
    define("ERROR_19","Unable to set ILOs in discussion.");define("DEBUGERROR_19","Unable to generate SimpleXMLElement in Discussion::setILOs().");
    define("ERROR_20","Unable to save discussion.");define("DEBUGERROR_20","Unable to save discussion in discussion.php switch.");
    define("ERROR_21","Unable to load discussion.");define("DEBUGERROR_21","Unable to load discussion from payload.");
    define("ERROR_22","Unable to autosave discussion.");define("DEBUGERROR_22","Unable to autosave discussion in discussion.php switch.");
    define("ERROR_23","Unable to load field.");define("DEBUGERROR_23","Unable to load from URI in Field::loadFromURI().");
    define("ERROR_24","Unable to load fields.");define("DEBUGERROR_24","Unable to load fields in fields.php.");
    define("ERROR_25","Unable to save ILO.");define("DEBUGERROR_25","Unable to insert ILO Ilo::save().");
    define("ERROR_26","Unable to delete old ILO.");define("DEBUGERROR_26","Unable to select ilo in Ilo::killIlo().");
    define("ERROR_27","Unable to recover old ILO.");define("DEBUGERROR_27","Unable to select ilo in Ilo::resurrectIlo().");
    define("ERROR_28","Unable to load ILO.");define("DEBUGERROR_28","Unable to set type ID with type name in Ilo::setTypeIdByName().");
    define("ERROR_29","Unable to load ILO.");define("DEBUGERROR_29","Unable to set type name with type ID in Ilo::setTypeNameById().");
    define("ERROR_30","Unable to load lesson.");define("DEBUGERROR_30","Unable to load lesson from URI in ilosByLesson.php.");
    define("ERROR_31","Unable to load image.");define("DEBUGERROR_31","Unable to load Image from Id in Image::loadFromId().");
    define("ERROR_32","Unable to save image.");define("DEBUGERROR_32","Unable to save image in image.php.");
    define("ERROR_33","Unable to save image.");define("DEBUGERROR_33","Unable to load image from payload in image.php.");
    define("ERROR_34","Invalid HTML.");define("DEBUGERROR_34","Invalid HTML in Lesson::loadFromPayload().");
    define("ERROR_35","Unable to get ID from URI.");define("DEBUGERROR_35","Unable to get URI from ID in Material::URIToId().");
    define("ERROR_36","Unable to save lesson.");define("DEBUGERROR_36","Unable to generate SimpleXMLElement in Lesson::loadFromPayload()");
    define("ERROR_37","Unable to load lesson.");define("DEBUGERROR_37","Unable to load lesson in Lesson::loadFromUri().");
    define("ERROR_38","Unable to save lesson.");define("DEBUGERROR_38","Unable to insert lesson into lesson table in Lesson::save().");
    define("ERROR_39","Unable to delete lesson.");define("DEBUGERROR_39","Unable to insert lesson into deleted_lessons in Lesson::delete().");
    define("ERROR_40","Unable to delete lesson.");define("DEBUGERROR_40","Unable to delete lesson from lesson table in Lesson:delete().");
    define("ERROR_41","Unable to reorder lessons after delete.");define("DEBUGERROR_41","Unable to reorder lessons in Lesson::delete().");
    define("ERROR_42","Unable to adjust lesson order.");define("DEBUGERROR_42","Unable to adjust lesson_order in Lesson::setPosition().");
    define("ERROR_43","Unable to adjust lesson order.");define("DEBUGERROR_43","Unable to adjust lesson_order in Lesson::setPosition().");
    define("ERROR_44","Lesson name already exists.");define("DEBUGERROR_44","Lesson name already exists in Lesson::rename().");
    define("ERROR_45","Unable to rename lesson.");define("DEBUGERROR_45","Unable to rename lesson in Lesson::rename().");
    define("ERROR_46","Unable to recover lesson.");define("DEBUGERROR_46","Unable to recover lesson from lesson_history in Lesson::recoverDeletedLessons().");
    define("ERROR_47","Unable to recover lesson.");define("DEBUGERROR_47","Unable to find deleted lesson in table deleted_lessons in Lesson::recoverDeletedLessons().");
    define("ERROR_48","No section in which to put lesson.");define("DEBUGERROR_48","Unable to get section_id in Lesson::recoverDeletedLessons().");
    define("ERROR_49","Unable to recover lesson.");define("DEBUGERROR_49","Unable to insert lesson in Lesson::recoverDeletedLessons() since name already exists in section.");
    define("ERROR_50","Unable to recover lesson.");define("DEBUGERROR_50","Unable to insert lesson in Lesson::recoverDeletedLessons().");
    define("ERROR_51","Unable to recover lesson.");define("DEBUGERROR_51"," Unable to delete row in deleted_lessons in Lesson::recoverDeletedLessons().");
    define("ERROR_52","Unable to save lesson ILOs.");define("DEBUGERROR_52","Unable to generate SimpleXMLElement in Lesson::setILOs().");
    define("ERROR_53","Lesson name already exists.");define("DEBUGERROR_53","Lesson name already exists in lesson.php.");
    define("ERROR_54","Unable to save new lesson.");define("DEBUGERROR_54","Unable to save new lesson in lesson switch in lesson.php.");
    define("ERROR_55","Unable to save lesson.");define("DEBUGERROR_55","Unable to update lesson in lesson switch in lesson.php.");
    define("ERROR_56","Unable to rename lesson.");define("DEBUGERROR_56","Unable to rename lesson in lesson switch in lesson.php.");
    define("ERROR_57","Unable to load lesson for saving.");define("DEBUGERROR_57","Unable to load from payload in lesson.php.");
    define("ERROR_58","Unable to autosave lesson.");define("DEBUGERROR_58","Unable to autosave lesson in lessonAutosave.php.");
    define("ERROR_59","Unable to get lesson history.");define("DEBUGERROR_59","Unable to get lesson history in lessonHistory.php.");
    define("ERROR_60","Unable to set lesson position.");define("DEBUGERROR_60","Unable to set lesson position in lessonPosition.php.");
    define("ERROR_61","Unable to set lesson position.");define("DEBUGERROR_61","Unable to load lesson from URI in lessonPosition.php.");
    define("ERROR_62","Unable to load URI.");define("DEBUGERROR_62","Invalid URI supplied in Material::URIToId().");
    define("ERROR_63","Unable to load revision.");define("DEBUGERROR_63","Unable to load revision in RevisionRow::loadFromUri().");
    define("ERROR_64","Unable to save lesson revision.");define("DEBUGERROR_64","Unable to insert revision row in RevisionRow::save().");
    define("ERROR_65","Unable to save lesson revision.");define("DEBUGERROR_65","Unable to retrieve max_revision_id in RevisionHistory::save().");
    define("ERROR_66","Unable to load section.");define("DEBUGERROR_66","Unable to load section from URI in Section::loadFromUri().");
    define("ERROR_67","Unable to save section.");define("DEBUGERROR_67","Unable to retrieve max section ID in Section::save().");
    define("ERROR_68","Unable to save section.");define("DEBUGERROR_68","Unable to save section in Section::save().");
    define("ERROR_69","Unable to delete section, since it still contains lessons.");define("DEBUGERROR_69","Unable to delete section, since it still contains lessons.");
    define("ERROR_70","Unable to delete section.");define("DEBUGERROR_70","Unable to delete section in Section::delete().");
    define("ERROR_71","Unable to change section order.");define("DEBUGERROR_71","Unable to adjust section_order in section talbe in Section::setPosition().");
    define("ERROR_72","Section name already exists.");define("DEBUGERROR_72","Unable to rename section in Section::rename() since section name already exists.");
    define("ERROR_73","Unable to rename section.");define("DEBUGERROR_73","Unable to rename section in Section::rename().");
    define("ERROR_74","Section name already exists.");define("DEBUGERROR_74"," Unable to create new section in section.php because section already exists.");
    define("ERROR_75","Unable to save section.");define("DEBUGERROR_75","Unable to save section in section.php.");
    define("ERROR_76","Unable to save section.");define("DEBUGERROR_76","Unable to load from payload in section.php.");
    define("ERROR_77","Unable to set section position.");define("DEBUGERROR_77","Unable to set section position in sectionPosition.php.");
    define("ERROR_78","Unable to set section position.");define("DEBUGERROR_78","Unable to load section in sectionPosition.php.");
    define("ERROR_79","Unable to load subject.");define("DEBUGERROR_79","Unable to load from URI in Subject::loadFromUri().");
    define("ERROR_80","Unable to save subject.");define("DEBUGERROR_80","Unable to load subject from payload in Subject::loadFromPayload().");
    define("ERROR_81","Unable to save subject.");define("DEBUGERROR_81","Unable to save subject in Subject::save().");
    define("ERROR_82","Unable to delete subject.");define("DEBUGERROR_82","Unable to delete subject in Subject::delete().");
    define("ERROR_83","Unable to create new username.");define("DEBUGERROR_83","Unable to insert username in User::save().");
    define("ERROR_84","Unable to create new username.");define("DEBUGERROR_84","Invalid provider name in User::save().");
    define("ERROR_85","OpenID already exists.");define("DEBUGERROR_85","OpenId already exists.");
    define("ERROR_86","Unable to create new username.");define("DEBUGERROR_86","Unable to select user_id from usernames in User::save().");
    define("ERROR_87","Unable to get user status.");define("DEBUGERROR_87","Unable to get user status in User::save().");
    define("ERROR_88","Unable to save user.");define("DEBUGERROR_88","Unable to insert user in User::save().");
    define("ERROR_89","Unable to convert username to ID.");define("DEBUGERROR_89","Unable to find username in User::usernameToId().");
    define("ERROR_90","Unable to load user.");define("DEBUGERROR_90","Unable to load user form URI in user.php.");
    define("ERROR_91","Unable to save user.");define("DEBUGERROR_91","Unable to load user from payload in user.php.");
    define("ERROR_92","Unable to delete discussion.");define("DEBUGERROR_92","Unable to delete discussion in Discussion::delete().");
    define("ERROR_93","Unable to save discussion.");define("DEBUGERROR_93","Unable to load discussion from payload in discussion.php.");
    define("ERROR_94","Unable to get discussion history.");define("DEBUGERROR_94","Unable to get discussion history in discussionHistory.php.");
    define("ERROR_95","Unable to delete ILO.");define("DEBUGERROR_95","Unable to delete ILO in ILO::delete().");
    define("ERROR_96","Unable to save image.");define("DEBUGERROR_96","Unable to insert image in images table in Image::save().");
    define("ERROR_97","Unable to save image.");define("DEBUGERROR_97","Unable to get ID from images table in Image::save().");
    define("ERROR_98","Invalid HTML.");define("DEBUGERROR_98","Invalid HTML in ContentAutosave::loadFromPayload()");
    define("ERROR_99","Invalid Content Type in URI.");define("DEBUGERROR_99","Invalid content type in URI.");
    define("ERROR_100","Lesson name already exists in that section.");define("DEBUGERROR_100","Unable to move set lesson position in Lesson::setPosition().");
    define("ERROR_101","Unable to recover lesson.");define("DEBUGERROR_101","Unable to get section_name in Lesson::recoverDeletedLessons().");
    define("ERROR_102","Unable to delete lesson.");define("DEBUGERROR_102","Unable to delete autosaved lesson in Lesson::delete().");
    define("ERROR_103","Unable to delete lesson.");define("DEBUGERROR_103","Unable to delete autosaved discussion in Lesson::delete().");
    define("ERROR_104","Unable to delete image.");define("DEBUGERROR_104","Unable to delete image from images table in Image::save().");
    define("ERROR_105","Unable to load Test Blueprint.");define("DEBUGERROR_105","Unable to load Test Blueprint from test_blueprint table in TestBlueprint::loadFromID().");
    define("ERROR_106","Unable to load image.");define("DEBUGERROR_106","Unable to load image_license in Image::save().");
    define("ERROR_107","Unsubmitted ILO in HTML.");define("DEBUGERROR_107","Unsubmitted ILO in HTML in Lesson::checkILOsExist.");
    define("ERROR_108","Unable to log in.");define("DEBUGERROR_108","Unable to set last_logged_in in users table in User::login.");
    define("ERROR_109","Unable to get next ILO Id.");define("DEBUGERROR_109","Unable to get next ILO Id in lesson.php.");
    define("ERROR_110","Unable to load citation.");define("DEBUGERROR_110","Unable to load citation by Id in Citation::loadFromId().");
    define("ERROR_111","Unable to load citation.");define("DEBUGERROR_111","Unable to load citation by Id in Citation::loadFromId().");
    define("ERROR_112","Unable to save citation.");define("DEBUGERROR_112","Unable to save citation in Citation::save()");
    define("ERROR_113","Unable to adjust lesson order.");define("DEBUGERROR_113","Unable to record position change in move_content table in Lesson::setPosition().");
    define("ERROR_114","Unable to adjust section order.");define("DEBUGERROR_114","Unable to record position change in move_content table in Section::setPosition().");
    define("ERROR_115","Unable to save question.");define("DEBUGERROR_115","Unable to save question in TempQuestion::save().");
    define("ERROR_116","Unable to delete question.");define("DEBUGERROR_116","Unable to delete question in TempQuestion::delete().");
    define("ERROR_117","Unable to submit answer.");define("DEBUGERROR_117","Unable to submit answer in TempQuestion::submitAnswer().");
    define("ERROR_118","Unable to delete user answer.");define("DEBUGERROR_118","Unable to delete user answer in TempQuestion::deleteUserAnswer().");
    define("ERROR_119","Unable to save lesson plan.");define("DEBUGERROR_119","Unable to save lesson plan in LessonPlanManager::save().");
    define("ERROR_120","Unable to add lesson plan tags.");define("DEBUGERROR_120","Unable to add lesson plan tags in LessonPlanManager::addTags().");
    define("ERROR_121","Unable to get lesson plan tags.");define("DEBUGERROR_121","Unable to get lesson plan tags in LessonPlanManager::getTagsByLessonPlanId().");
    define("ERROR_122","Unable to delete lesson plan.");define("DEBUGERROR_122","Unable to delete lesson plan in LessonPlanManager::delete().");
    define("ERROR_123","Unable to remove lesson plan tags.");define("DEBUGERROR_123","Unable to remove lesson plan tags in LessonPlanManager::removeTagAttachments().");
    define("ERROR_124","Unable to load lesson plan.");define("DEBUGERROR_124","Unable to load lesson plan in LessonPlanManager::loadFromId().");
    define("ERROR_125","Unable to save content.");define("DEBUGERROR_125","Unable to load content from payload in Content::loadFromPayload() since it contains scripts.");
    define("ERROR_126","Unable to load content.");define("DEBUGERROR_126","Unable to load content from Id in Content::loadFromId().");
    define("ERROR_127","Unable to save lesson addition content.");define("DEBUGERROR_127","Unable to save lesson addition content in LessonBoundContent::save().");
    define("ERROR_128","Unable to delete lesson addition content.");define("DEBUGERROR_128","Unable to delete lesson addition content in LessonBoundContent::delete().");
    define("ERROR_129","Unable to load video.");define("DEBUGERROR_129","Unable to load video from URI in Video::loadFromUri().");
    define("ERROR_130","Unable to load video.");define("DEBUGERROR_130","Unable to load video from URI in Video::loadFromId().");
    define("ERROR_131","Unable to save video.");define("DEBUGERROR_131","Unable to save video in Video::save().");
    define("ERROR_132","Unable to delete video.");define("DEBUGERROR_132","Unable to delete video in Video::delete().");